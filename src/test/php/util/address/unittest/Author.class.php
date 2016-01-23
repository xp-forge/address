<?php namespace util\address\unittest;

use lang\partial\Builder;
use lang\partial\Value;

class Author implements \lang\Value {
  use Author\including\Builder;
  use Author\including\Value;

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