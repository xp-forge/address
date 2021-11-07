<?php namespace util\address;

class Pair {
  public $path, $content, $value;

  /**
   * Creates a new pair
   *
   * @param  var $path
   * @param  var $content
   * @param  ?array<var> $value
   */
  public function __construct($path, $content, $value= null) {
    $this->path= $path;
    $this->content= $content;
    $this->value= $value;
  }
}