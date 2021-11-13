<?php namespace util\address;

use io\streams\InputStream;
use util\Objects;

/**
 * XML stream input
 *
 * @test  xp://util.address.unittest.XmlInputTest
 */
class XmlStream extends Address {
  private $in;

  /**
   * Creates a new stream-based XML input
   *
   * @param  io.streams.InputStream $in
   */
  public function __construct(InputStream $in) {
    $this->in= $in;
  }

  /** @return io.streams.InputStream */
  protected function stream() { return $this->in; }

  /** @return string */
  public function toString() {
    return nameof($this).'<'.$this->in->toString().'>';
  }

  /** @return string */
  public function hashCode() {
    return 'S'.Objects::hashOf($this->file);
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->in, $value->in) : 1;
  }
}