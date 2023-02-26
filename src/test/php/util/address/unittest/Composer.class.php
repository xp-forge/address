<?php namespace util\address\unittest;

use lang\Value;
use util\Objects;

class Composer implements Value {
  private $name, $type, $keywords, $requirements;

  public function __construct($name, $type, $keywords= [], $requirements= []) {
    $this->name= $name;
    $this->type= $type;
    $this->keywords= $keywords;
    $this->requirements= $requirements;
  }

  /** @return string */
  public function hashCode() { return 'C'.Objects::hashOf((array)$this); }

  /** @return string */
  public function toString() { return nameof($this).'@'.Objects::stringOf(get_object_vars($this)); }

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