<?php namespace util\address\unittest;

use lang\Value;
use util\Objects;
use util\address\WithCreation;

class Book implements Value {
  use WithCreation;

  private $name, $author;

  /**
   * Creates a new book
   *
   * @param  string $name
   * @param  util.data.unittest.Author $author
   */
  public function __construct($name, Author $author= null) {
    $this->name= $name;
    $this->author= $author;
  }

  /** @return string */
  public function hashCode() {
    return 'B#'.crc32($this->name).($this->author ? $this->author->hashCode() : '');
  }

  /** @return string */
  public function toString() {
    return nameof($this).'("'.$this->name.'"'.($this->author ? ' by '.$this->author->toString() : '').')';
  }

  /**
   * Compares this
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare((array)$this, (array)$value) : 1;
  }
}