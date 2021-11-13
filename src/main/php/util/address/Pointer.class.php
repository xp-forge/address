<?php namespace util\address;

class Pointer {
  private $address;

  /** @param util.address.Address */
  public function __construct($address) {
    $this->address= $address;
  }

  /**
   * Returns the current value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @return var
   * @throws util.NoSuchElementException if there are no more elements
   */
  public function value(Definition $definition= null) {
    $it= $this->address->getIterator(true);
    return null === $definition ? $it->current() : $it->value($definition, $this->address, '/', true);
  }
}