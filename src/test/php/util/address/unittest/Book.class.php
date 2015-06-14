<?php namespace util\address\unittest;

use lang\partial\ValueObject;
use lang\partial\WithCreation;

class Book extends \lang\Object {
  use Book\including\ValueObject;
  use Book\including\WithCreation;

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