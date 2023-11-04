<?php namespace util\address;

class Iteration {
  private $it;
  public $tokens= [];

  /**
   * Creates an iteration
   *
   * @param  util.address.StreamIterator $it
   * @param  string $base
   */
  public function __construct($it) {
    $this->it= $it;
  }

  /** @return util.address.StreamIterator */
  public function iterator() { return $this->it; }

  /** @return bool */
  public function valid() { return $this->it->valid(); }

  /** @return string */
  public function path() { return $this->it->valid() ? $this->it->key() : null; }

  /**
   * Returns the next value according to the given definition.
   *
   * @param  util.address.Definition $definition
   * @return var
   */
  public function next(Definition $definition= null) {
    $this->tokens[]= $this->it->token;

    $value= null === $definition ? $this->it->current() : $this->it->value($definition, false);
    $this->it->next();
    return $value;
  }
}
