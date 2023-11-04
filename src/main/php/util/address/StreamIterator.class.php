<?php namespace util\address;

use Iterator, ReturnTypeWillChange;

/**
 * Abstract base class for format-based iterators. Subclasses implement
 * the `nextToken()` method to return the next token.
 *
 * @see  util.address.JsonIterator
 * @see  util.address.XmlIterator
 */
abstract class StreamIterator implements Iterator {
  const SEPARATOR= '/';

  public $token;
  protected $input;
  protected $path= null;
  protected $tokens= [];

  /** @param text.Tokenizer $input */
  public function __construct($input) {
    $this->input= $input;
  }

  /** @return ?util.address.Token */
  protected abstract function nextToken();

  /**
   * Creates value from definition.
   *
   * @param  util.address.Definition $definition
   * @param  bool $source
   * @return var
   */
  public function value($definition, $source) {
    if (null === $this->token->source) {
      $token= $this->token;

      // Create value, storing tokens during the iteration
      $iteration= new Iteration($this);
      $value= $definition->create($iteration);

      // Unless we are at the end of the stream, push back last token.
      $this->token && array_unshift($this->tokens, $this->token);
      $this->token= $source ? $token->from($iteration->tokens) : $token;
      return $value;
    } else {

      // Restore tokens consumed by previous iteration
      $this->tokens= array_merge($this->token->source, [$this->token], $this->tokens);
      $this->token= array_shift($this->tokens);
      return $definition->create(new Iteration($this));
    }
  }

  /** @return void */
  #[ReturnTypeWillChange]
  public function rewind() {
    if (null !== $this->path) {
      $this->input->reset();
    }

    $this->path= '';
    $this->token= $this->nextToken();
  }

  /** @return string */
  #[ReturnTypeWillChange]
  public function current() {
    return $this->token->content;
  }

  /** @return string */
  #[ReturnTypeWillChange]
  public function key() {
    return $this->token->path;
  }

  /** @return void */
  #[ReturnTypeWillChange]
  public function next() {
    $this->token= $this->nextToken();
  }

  /** @return bool */
  #[ReturnTypeWillChange]
  public function valid() {
    return null !== $this->token;
  }
}