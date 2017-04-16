<?php namespace util\address;

class Iteration extends \lang\Object {
  private $input, $base;

  /**
   * Creates an iteration
   *
   * @param  util.address.Address $input
   * @param  string $base
   */
  public function __construct(Address $input, $base) {
    $this->input= $input;
    $this->base= $base.'/';
  }

  /** @return util.address.Address */
  public function input() { return $this->input; }

  /** @return string */
  public function base() { return $this->base; }

  /** @return bool */
  public function valid() { return $this->input->valid(); }

  /** @return string */
  public function path() { return $this->input->path(); }

  /**
   * Returns the next value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @param  string $base If omitted, will use current path for recursion
   * @return var
   * @throws util.NoSuchElementException if there are no more eements
   */
  public function next(Definition $definition= null, $base= null) {
    return $this->input->next($definition, $base ?: $this->path());
  }
}