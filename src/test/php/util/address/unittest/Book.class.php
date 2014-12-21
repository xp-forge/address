<?php namespace util\address\unittest;

use util\Objects;

class Book extends \lang\Object { use \util\objects\CreateWith;
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
  public function name() { return $this->name; }

  /** @return util.data.unittest.Author */
  public function author() { return $this->author; }

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
    return $value instanceof self && $this->name === $value->name && Objects::equal($this->author, $value->author);
  }
}