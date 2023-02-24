<?php namespace util\address;

class Pointer {
  private $streaming;

  /** Creates a pointer to a given streaming */
  public function __construct(streaming $streaming) {
    $this->streaming= $streaming;
  }

  /** @return util.address.Streaming */
  public function streaming() { return $this->streaming; }

  /**
   * Returns the current value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @return var
   * @throws util.NoSuchElementException if there are no more elements
   */
  public function value(Definition $definition= null) {
    $it= $this->streaming->getIterator(true);
    return null === $definition ? $it->current() : $it->value($definition, $this->streaming, true);
  }
}