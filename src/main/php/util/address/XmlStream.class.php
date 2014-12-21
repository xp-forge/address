<?php namespace util\address;

use io\streams\InputStream;

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

  /** @return php.Iterator */
  protected function newIterator() { return new XmlIterator($this->in); }
}