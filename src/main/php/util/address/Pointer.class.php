<?php namespace util\address;

class Pointer {
  private $address;

  /** Creates a pointer to a given address */
  public function __construct(Address $address) {
    $this->address= $address;
  }

  /** @return util.address.Address */
  public function address() { return $this->address; }

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