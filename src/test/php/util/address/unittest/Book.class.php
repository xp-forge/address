<?php namespace util\address\unittest;

use util\Objects;
use lang\partial\WithCreation;
use lang\partial\ValueObject;

class Book extends \lang\Object {
  use Book\including\WithCreation;
  use Book\including\ValueObject;

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