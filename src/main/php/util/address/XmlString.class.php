<?php namespace util\address;

use io\streams\MemoryInputStream;
use lang\Value;

/**
 * XML string input
 *
 * @test  util.address.unittest.XmlInputTest
 */
class XmlString extends Address implements Value {
  private $input;

  /**
   * Creates a new file-based XML input
   *
   * @param  string $input
   */
  public function __construct($input) {
    $this->input= $input;
  }

  /** @return io.streams.InputStream */
  protected function stream() { return new MemoryInputStream($this->input); }

  /** @return string */
  public function toString() {
    $l= strlen($this->input);
    return nameof($this).'<"'.($l > 10 ? substr($this->input, 0, 10).'..." ('.$l.' bytes)>' : $this->input.'">');
  }

  /** @return string */
  public function hashCode() {
    return md5($this->input);
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->input <=> $value->input : 1;
  }
}