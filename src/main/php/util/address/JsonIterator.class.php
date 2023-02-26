<?php namespace util\address;

use Iterator, ReturnTypeWillChange;
use io\streams\InputStream;
use lang\FormatException;
use text\StreamTokenizer;

/**
 * XML stream iterator
 *
 * @test  util.address.unittest.JsonIteratorTest
 */
class JsonIterator extends StreamIterator {
  private $it;

  /**
   * Creates a new JSON iterator on a given stream
   *
   * @param  io.streams.InputStream $input If seekable, this iterator will be rewindable.
   */
  public function __construct(InputStream $input) {
    parent::__construct(new StreamTokenizer($input, ":{}[]\"\r\n\t ", true));
  }

  /**
   * Fetches next token, skipping over whitespace
   * 
   * @param  ?string $delimiters
   * @return ?string
   */
  protected function token($delimiters= null) {
    do {
      $token= $this->input->nextToken($delimiters ?? $this->input->delimiters);
    } while (null !== $token && 0 === strcspn($token, "\r\n\t "));
    return $token;
  }

  /**
   * Reads a string, handling escape sequences
   *
   * @return string
   */
  protected function string() {
    $n= $this->input->nextToken('"');
    if ('"' === $n) return '';

    $s= '';
    do {
      $s.= $n;
      if ('\\' !== $n[strlen($n) - 1]) break;

      $s.= $this->input->nextToken('"');
      $n= $this->input->nextToken('"');
    } while ($this->input->hasMoreTokens());

    $this->input->nextToken('"');
    return json_decode('"'.$s.'"');
  }

  /**
   * Yields values based on a given token, destructuring lists and maps
   * into their components.
   * 
   * @param  string $token
   * @return iterable
   */
  protected function iterate($token) {
    if ('{' === $token) {
      yield $this->path => null;

      $next= $this->token();
      while ('}' !== $next && $this->input->hasMoreTokens()) {
        $this->path.= self::SEPARATOR.strtr($this->string(), self::SEPARATOR, "\x1D");
        $this->token(':');

        foreach ($this->iterate($this->token()) as $value) {
          yield $this->path => $value;
        }

        $this->path= substr($this->path, 0, strrpos($this->path, self::SEPARATOR));
        if (',' === ($next= $this->token(',}'))) {
          $next= $this->token();
        }
      }
    } else if ('[' === $token) {
      yield $this->path => null;

      $next= $this->token();
      $i= 0;
      while (']' !== $next && $this->input->hasMoreTokens()) {
        $this->path.= self::SEPARATOR.'[]';

        foreach ($this->iterate($next) as $value) {
          yield $this->path => $value;
        }

        $this->path= substr($this->path, 0, strrpos($this->path, self::SEPARATOR));
        if (',' === ($next= $this->token(',]'))) {
          $next= $this->token();
        }
      }
    } else if ('"' === $token) {
      yield $this->string();
    } else if ('null' === $token) {
      yield null;
    } else if ('false' === $token) {
      yield false;
    } else if ('true' === $token) {
      yield true;
    } else if (0 === strcspn($token, '.0123456789eE+-')) {
      yield strlen($token) === strcspn($token, '.eE') ? (int)$token : (float)$token;
    } else {
      throw new FormatException('Unexpected token `'.$token.'`');
    }
  }

  /** @return ?util.address.Token */
  protected function nextToken() {
    if ($this->tokens) return array_shift($this->tokens);

    if (null === $this->it) {
      $this->path= self::SEPARATOR;
      $this->it= $this->iterate($this->token());
    } else {
      $this->it->next();
    }

    return $this->it->valid() ? new Token($this->it->key(), $this->it->current()) : null;
  }
}