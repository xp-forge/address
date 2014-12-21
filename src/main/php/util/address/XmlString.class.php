<?php namespace util\address;

use io\streams\MemoryInputStream;

/**
 * XML string input
 *
 * @test  xp://util.address.unittest.XmlInputTest
 */
class XmlString extends Address {
  private $stream;

  /**
   * Creates a new file-based XML input
   *
   * @param  string $input
   */
  public function __construct($input) {
    $this->stream= new MemoryInputStream($input);
  }

  /** @return php.Iterator */
  protected function newIterator() { return new XmlIterator($this->stream); }
}