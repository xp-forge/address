<?php namespace util\address;

use lang\IllegalStateException;
use io\streams\InputStream;
use io\streams\Seekable;
use util\collections\Pair;
use text\StreamTokenizer;
use lang\FunctionType;

/**
 * XML stream iterator
 *
 * @test  xp://util.address.unittest.XmlIteratorTest
 */
class XmlIterator extends \lang\Object implements \Iterator {
  const SEPARATOR= '/';

  private $input, $path, $valid, $encoding= 'utf-8', $pairs= [];

  /**
   * Creates a new XML iterator on a given stream
   *
   * @param  io.streams.InputStream $input If seekable, this iterator will be rewindable.
   */
  public function __construct(InputStream $input) {
    $this->input= new StreamTokenizer($input, '<>', true);
    $this->path= null;
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
      if (preg_match('/encoding\s*=\s*["\']([^"\']+)["\']/i', $attr, $matches)) {
        $this->encoding= strtolower($matches[1]);
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

    array_unshift($this->pairs, new Pair($this->path, null));
    $this->valid= true;
  }

  /**
   * Handle CDATA section
   *
   * @param  string $content
   * @return void
   */
  protected function cdata($content) {
    if ($this->pairs) {
      $this->pairs[0]->value.= $content;
    }
  }

  /**
   * Handle parsed character data
   *
   * @param  string $content
   * @return void
   */
  protected function pcdata($content) {
    if ($this->pairs && '' !== ($t= trim($content))) {
      $this->pairs[0]->value.= $t;
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
    return iconv($this->encoding, \xp::ENCODING, substr($token, 0, $l));
  }

  /** @return util.collections.Pair */
  protected function token() {
    if (empty($this->pairs)) {

      $this->valid= false;
      while ($this->input->hasMoreTokens()) {
        $token= $this->input->nextToken();

        if ('<' === $token) {
          $tag= $this->input->nextToken('>');
          $this->input->nextToken('>');

          if ('?' === $tag{0}) {
            $p= strcspn($tag, ' ?', 1);
            $this->pi(substr($tag, 1, $p), substr($tag, $p + 1, - 1));
          } else if ('!' === $tag{0}) {
            if (0 === strncmp('![CDATA[', $tag, 8)) {
              $this->cdata($this->tokenUntil(substr($tag, 8), ']]>'));
            } else if (0 === strncmp('!--', $tag, 3)) {
              $this->comment($this->tokenUntil(substr($tag, 3), '-->'));
            } else {
              throw new IllegalStateException('Cannot handle '.$tag);
            }
          } else if ('/' === $tag{0}) {
            $this->close();
            if ($this->valid) break;
          } else {
            $p= strcspn($tag, ' /');
            $this->open(substr($tag, 0, $p), substr($tag, $p + 1));
            if ('/' === $tag{strlen($tag) - 1}) {
              $this->close();
              break;
            }
          }
        } else {
          $this->pcdata(html_entity_decode(iconv($this->encoding, \xp::ENCODING, $token), ENT_XML1 | ENT_QUOTES, \xp::ENCODING));
        }
      }
    }

    $pair= array_pop($this->pairs);
    // echo "<<< ", $pair ? $pair->toString() : "(null)", "\n";
    return $pair;
  }

  /** @return void */
  public function rewind() {
    if (null !== $this->path) {
      $this->input->reset();
    }

    $this->path= '';
    $this->token= $this->token();
  }

  /** @return string */
  public function current() {
    return $this->token->value;
  }

  /** @return string */
  public function key() {
    return $this->token->key;
  }

  /** @return void */
  public function next() {
    $this->token= $this->token();
  }

  /** @return bool */
  public function valid() {
    return $this->valid;
  }
}