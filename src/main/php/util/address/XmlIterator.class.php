<?php namespace util\address;

use Iterator, ReturnTypeWillChange;
use io\streams\{InputStream, Seekable};
use lang\{FunctionType, FormatException, IllegalStateException};
use text\{StreamTokenizer, StringTokenizer};

/**
 * XML stream iterator
 *
 * @test  xp://util.address.unittest.XmlIteratorTest
 */
class XmlIterator extends StreamIterator {
  private $valid, $node;
  private $encoding= 'utf-8';
  private $entities= ['amp' => '&', 'apos' => "'", 'quot' => '"', 'gt' => '>', 'lt' => '<'];

  /**
   * Creates a new XML iterator on a given stream
   *
   * @param  io.streams.InputStream $input If seekable, this iterator will be rewindable.
   */
  public function __construct(InputStream $input) {
    $this->input= new StreamTokenizer($input, '<>', true);
  }

  /**
   * Decodes a given string value, taking into account the doctype entities
   *
   * @param  string $value
   * @return string
   */
  protected function decode($value) {
    return preg_replace_callback(
      '/&([#a-zA-Z0-9_:.-]+);/',
      function($m) { return $this->entities[$m[1]] ?? html_entity_decode($m[0], ENT_XML1 | ENT_SUBSTITUTE, \xp::ENCODING); },
      $this->encoding === \xp::ENCODING ? $value : iconv($this->encoding, \xp::ENCODING, $value)
    );
  }

  /**
   * Handle processing instruction
   *
   * @param  string $name
   * @param  string $attr
   * @return void
   */
  protected function pi($name, $attr) {
    if ('xml' === $name) {
      $attr= $this->attributesIn($attr);
      if (isset($attr['encoding'])) {
        $this->encoding= strtolower($attr['encoding']);
      }
    }
  }

  /**
   * Handle opening a tag
   *
   * @param  string $name
   * @param  string $attr
   * @return void
   */
  protected function open($name, $attr) {
    if ('' === $this->path) {
      $this->path= self::SEPARATOR;
    } else {
      $this->path.= self::SEPARATOR.$name;
    }

    $this->node= sizeof($this->tokens);
    $this->tokens[]= $token= new Token($this->path, null);
    if ('' !== trim($attr)) {
      foreach ($this->attributesIn($attr) as $name => $value) {
        if ('xml:space' === $name) {
          $token->space= 'preserve' === $value;
        } else {
          $this->tokens[]= new Token($this->path.'/@'.$name, $value);
        }
      }
    }
    $this->valid= true;
  }

  /**
   * Handle CDATA section
   *
   * @param  string $content
   * @return void
   */
  protected function cdata($content) {
    if ($token= $this->tokens[$this->node] ?? null) {
      $token->content.= $content;
    }
  }

  /**
   * Handle parsed character data
   *
   * @param  string $content
   * @return void
   */
  protected function pcdata($content) {
    if ($token= $this->tokens[$this->node] ?? null) {
      if ('' !== ($t= $token->space ? $content : trim($content))) {
        $token->content.= $t;
      }
    }
  }

  /**
   * Handle comments
   *
   * @param  string $content
   * @return void
   */
  protected function comment($content) {
    // NOOP
  }

  /**
   * Handle external doctype. Always throws an exception.
   *
   * @param  string $source
   * @return void
   */
  protected function doctype($source) {
    throw new IllegalStateException('External doctypes not supported');
  }

  /**
   * Handle doctype, parsing its internal entities declarations.
   *
   * @see    https://xmlwriter.net/xml_guide/entity_declaration.shtml
   * @param  string $doctype
   * @return void
   */
  protected function declare($doctype) {

    // No need to support parameter entities, see https://stackoverflow.com/a/39549669
    preg_match_all('/<!ENTITY\s+([^ ]+)\s+"([^"]+)">/', $doctype, $matches, PREG_SET_ORDER);

    // Decode known entities referenced inside declarations first
    $declarations= [];
    foreach ($matches as $match) {
      $declarations[$match[1]]= html_entity_decode($match[2], ENT_XML1 | ENT_SUBSTITUTE, \xp::ENCODING);
    }

    // The only entities left over now are references to the one inside this DTD.
    // Resolve them, protecting against infinite recursion!
    $resolve= function($declaration, $stack) use(&$resolve, &$declarations) {
      preg_match_all('/&([a-zA-Z0-9_:.-]+);/', $declaration, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        if (isset($stack[$match[0]])) {
          throw new FormatException('Entity reference loop '.implode(' > ', array_keys($stack)).' > '.$match[0]);
        } else if (null === ($resolved= $declarations[$match[1]] ?? null)) {
          throw new FormatException('Entity '.$match[0].' not defined');
        }

        $stack[$match[0]]= true;
        $declaration= str_replace($match[0], $resolve($resolved, $stack), $declaration);
        unset($stack[$match[0]]);
      }
      return $declaration;
    };
    foreach ($declarations as $name => $declaration) {
      $declarations[$name]= $resolve($declaration, []);
    }

    // Finally, merge resolved declarations into entities without overwriting
    $this->entities+= $declarations;
  }

  /**
   * Handle closing a tag
   *
   * @return void
   */
  protected function close() {
    $this->path= substr($this->path, 0, strrpos($this->path, '/'));
  }

  /**
   * Parse until a given end token
   *
   * @param  string $begin
   * @param  string $end
   * @return string
   */
  protected function tokenUntil($begin, $end) {
    $token= $begin.'>';
    $l= -strlen($end);
    while (0 !== substr_compare($token, $end, $l) && $this->input->hasMoreTokens()) {
      $token.= $this->input->nextToken('>');
    }
    return $this->encoding === \xp::ENCODING
      ? substr($token, 0, $l)
      : iconv($this->encoding, \xp::ENCODING, substr($token, 0, $l))
    ;
  }

  /**
   * Parse attributes
   *
   * @param  string $string
   * @return [:string]
   */
  protected function attributesIn($string) {
    $st= new StringTokenizer($string, '="\'', true);
    $attributes= [];
    while ($st->hasMoreTokens()) {
      $token= $st->nextToken();
      if ('=' === $token) {
        $name= trim($last);
      } else if ('"' === $token || "'" === $token) {
        $value= $st->nextToken($token);
        $st->nextToken($token);
        $attributes[$name]= $this->decode($value);
      } else {
        $last= $token;
      }
    }
    return $attributes;
  }

  /** @return ?util.address.Token */
  protected function nextToken() {
    if (empty($this->tokens)) {
      $this->valid= false;
      while (null !== ($token= $this->input->nextToken())) {
        if ('<' === $token) {
          $tag= $this->input->nextToken('>');
          $this->input->nextToken('>');

          if ('?' === $tag[0]) {
            $p= strcspn($tag, ' ?', 1);
            $this->pi(substr($tag, 1, $p), substr($tag, $p + 1, - 1));
          } else if ('!' === $tag[0]) {
            if (0 === strncmp('![CDATA[', $tag, 8)) {
              $this->cdata($this->tokenUntil(substr($tag, 8), ']]>'));
            } else if (0 === strncmp('!--', $tag, 3)) {
              $this->comment($this->tokenUntil(substr($tag, 3), '-->'));
            } else if (0 === strncmp('!DOCTYPE', $tag, 8)) {
              if (false === ($p= strpos($tag, '[', 8))) {
                $this->doctype(substr($tag, 8));
              } else {
                $this->declare($this->tokenUntil(substr($tag, $p + 1), ']>'));
              }
            } else {
              throw new IllegalStateException('Cannot handle '.$tag);
            }
          } else if ('/' === $tag[0]) {
            $this->close();
            if ($this->valid) break;
          } else {
            $p= strcspn($tag, ' /');
            $this->open(substr($tag, 0, $p), substr($tag, $p + 1));
            if ('/' === $tag[strlen($tag) - 1]) {
              $this->close();
              break;
            }
          }
        } else if (null !== $token) {
          $this->pcdata($this->decode($token));
        }
      }
    }

    $token= array_shift($this->tokens);
    // echo "<<< ", $token ? "token<{$token->path}= {$token->content}>" : "(null)", "\n";
    return $token;
  }
}