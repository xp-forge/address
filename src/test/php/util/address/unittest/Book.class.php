<?php namespace util\address\unittest;

use lang\partial\Builder;
use lang\partial\Value;

class Book implements \lang\Value {
  use Book\including\Builder;
  use Book\including\Value;

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
}