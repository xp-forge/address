<?php namespace util\address;

class Pair {
  public $key, $value;

  /**
   * Creates a new pair
   *
   * @param  var $key
   * @param  var $value
   */
  public function __construct($key, $value) {
    $this->key= $key;
    $this->value= $value;
  }
}