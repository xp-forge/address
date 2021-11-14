<?php namespace util\address;

class Iteration {
  private $address;
  public $tokens= [];

  /**
   * Creates an iteration
   *
   * @param  util.address.Address $address
   * @param  string $base
   */
  public function __construct(Address $address) {
    $this->address= $address;
  }

  /** @return util.address.Address */
  public function address() { return $this->address; }

  /** @return bool */
  public function valid() { return $this->address->valid(); }

  /** @return string */
  public function path() { return $this->address->path(); }

  /**
   * Returns the next value according to the given definition.
   *
   * @param  util.address.Definition $definition
   * @return var
   */
  public function next(Definition $definition= null) {
    $it= $this->address->getIterator(true);
    $this->tokens[]= $it->token;

    $value= null === $definition ? $it->current() : $it->value($definition, $this->address, false);
    $it->next();
    return $value;
  }
}
