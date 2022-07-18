<?php namespace util\address;

use io\streams\InputStream;
use lang\Value;
use util\Objects;

/**
 * XML stream input
 *
 * @test  util.address.unittest.XmlStreamTest
 * @test  util.address.unittest.XmlInputTest
 */
class XmlStream extends Address implements Value {
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

    // Most InputStream instances have a toString() method but do not implement
    // the Value interface, see https://github.com/xp-framework/core/issues/310
    if ($this->in instanceof Value || method_exists($this->in, 'toString')) {
      return nameof($this).'<'.$this->in->toString().'>';
    } else {
      return nameof($this).'<'.Objects::stringOf($this->in).'>';
    }
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