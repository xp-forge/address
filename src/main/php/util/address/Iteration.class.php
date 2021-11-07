<?php namespace util\address;

class Iteration {
  private $input, $base;
  public $tokens= [];

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
   * Returns the next value according to the given definition.
   *
   * @param  util.address.Definition $definition
   * @return var
   */
  public function next(Definition $definition= null) {
    $it= $this->input->getIterator(true);
    $this->tokens[]= $it->token;

    $value= null === $definition ? $it->current() : $it->value($definition, $this->input, $this->base);
    $it->next();
    return $value;
  }
}
