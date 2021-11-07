<?php namespace util\address;

class Token {
  public $path, $content, $value;

  /**
   * Creates a new token
   *
   * @param  string $path
   * @param  var $content
   * @param  ?array<var> $value
   */
  public function __construct($path, $content, $value= null) {
    $this->path= $path;
    $this->content= $content;
    $this->value= $value;
  }
}