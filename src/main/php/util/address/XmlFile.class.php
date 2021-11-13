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

  /** @return io.streams.InputStream */
  protected function stream() { return $this->file->in(); }
}