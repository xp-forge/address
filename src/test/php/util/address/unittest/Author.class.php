<?php namespace util\address\unittest;

use util\Objects;
use lang\partial\WithCreation;

class Author extends \lang\Object {
  use Author\including\WithCreation;
  private $name;

  /**
   * Creates a new author
   *
   * @param  string $name
   */
  public function __construct($name) {
    $this->name= $name;
  }

  /** @return string */
  public function name() { return $this->name; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return $this->getClassName().'@'.Objects::stringOf(get_object_vars($this));
  }

  /**
   * Checks for equality
   *
   * @param  var $value
   * @return bool
   */
  public function equals($value) {
    return $value instanceof self && $this->name === $value->name;
  }
}