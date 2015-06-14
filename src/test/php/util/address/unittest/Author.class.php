<?php namespace util\address\unittest;

use lang\partial\ValueObject;
use lang\partial\WithCreation;

class Author extends \lang\Object {
  use Author\including\ValueObject;
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
}