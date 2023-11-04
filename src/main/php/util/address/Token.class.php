<?php namespace util\address;

class Token {
  public $path, $content;
  public $source= null;
  public $space= false;

  /**
   * Creates a new token
   *
   * @param  string $path
   * @param  var $content
   */
  public function __construct($path, $content) {
    $this->path= $path;
    $this->content= $content;
  }

  /**
   * Sets source
   *
   * @param  util.address.Token[] $source
   * @return self
   */
  public function from($source) {
    $this->source= $source;
    return $this;
  }
}