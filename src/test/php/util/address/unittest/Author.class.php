<?php namespace util\address\unittest;

use lang\Value;
use util\address\WithCreation;

class Author implements Value {
  use WithCreation;

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
  public function hashCode() { return 'A#'.crc32($this->name); }

  /** @return string */
  public function toString() { return nameof($this).'("'.$this->name.'")'; }

  /**
   * Compares this
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->name <=> $value->name : 1;
  }
}