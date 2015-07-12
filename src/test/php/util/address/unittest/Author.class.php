<?php namespace util\address\unittest;

use util\Objects;
use lang\partial\WithCreation;
use lang\partial\ValueObject;

class Author extends \lang\Object {
  use Author\including\WithCreation;
  use Author\including\ValueObject;

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