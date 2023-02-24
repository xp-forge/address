<?php namespace util\address;

use Iterator;
use io\streams\MemoryInputStream;

/**
 * XML string input
 *
 * @test  util.address.unittest.XmlInputTest
 * @deprecated by XmlStreaming
 */
class XmlString extends Address {

  /**
   * Creates a new file-based XML input
   *
   * @param  string $input
   */
  public function __construct($input) {
    parent::__construct(new MemoryInputStream($input));
  }

  /** Iterator implementation */
  protected function iterator(): Iterator { return new XmlIterator($this->stream); }
}