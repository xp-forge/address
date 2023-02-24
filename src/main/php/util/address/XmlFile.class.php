<?php namespace util\address;

use Iterator;
use io\streams\FileInputStream;

/**
 * XML file input
 *
 * @deprecated by XmlStreaming
 * @test  util.address.unittest.XmlInputTest
 */
class XmlFile extends Address {

  /**
   * Creates a new file-based XML input
   *
   * @param  string|io.Path|io.File $file
   */
  public function __construct($file) {
    parent::__construct(new FileInputStream($file));
  }

  /** Iterator implementation */
  protected function iterator(): Iterator { return new XmlIterator($this->stream); }

}