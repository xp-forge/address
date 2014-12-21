<?php namespace util\address;

use io\File;

/**
 * XML file input
 *
 * @test  xp://util.address.unittest.XmlInputTest
 */
class XmlFile extends Address {
  private $file;

  /**
   * Creates a new file-based XML input
   *
   * @param  io.File $file
   */
  public function __construct(File $file) {
    $this->file= $file;
  }

  /** @return php.Iterator */
  protected function newIterator() { return new XmlIterator($this->file->in()); }
}