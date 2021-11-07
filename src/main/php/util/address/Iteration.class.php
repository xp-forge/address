<?php namespace util\address;

class Iteration {
  private $input, $base;

  /**
   * Creates an iteration
   *
   * @param  util.address.XmlIterator $input
   * @param  string $base
   */
  public function __construct(XmlIterator $input, $base) {
    $this->input= $input;
    $this->base= $base;
  }

  /** @return util.address.XmlIterator */
  public function input() { return $this->input; }

  /** @return string */
  public function base() { return $this->base; }

  /** @return bool */
  public function valid() { return $this->input->valid(); }

  /** @return string */
  public function path() { return $this->input->valid() ? $this->input->key() : null; }

  /**
   * Returns the next value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @return var
   */
  public function next(Definition $definition= null) {
    try {
      return $this->input->value($definition, $this->base.'/');
    } finally {
      $this->input->next();
    }
  }
}
