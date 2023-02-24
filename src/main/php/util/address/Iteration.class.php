<?php namespace util\address;

class Iteration {
  private $streaming;
  public $tokens= [];

  /**
   * Creates an iteration
   *
   * @param  util.address.Streaming $streaming
   * @param  string $base
   */
  public function __construct(Streaming $streaming) {
    $this->streaming= $streaming;
  }

  /** @return util.address.Streaming */
  public function streaming() { return $this->streaming; }

  /** @return bool */
  public function valid() { return $this->streaming->valid(); }

  /** @return string */
  public function path() { return $this->streaming->path(); }

  /**
   * Returns the next value according to the given definition.
   *
   * @param  util.address.Definition $definition
   * @return var
   */
  public function next(Definition $definition= null) {
    $it= $this->streaming->getIterator(true);
    $this->tokens[]= $it->token;

    $value= null === $definition ? $it->current() : $it->value($definition, $this->streaming, false);
    $it->next();
    return $value;
  }
}
