<?php namespace util\address;

use io\File;
use lang\Value;

/**
 * XML file input
 *
 * @test  util.address.unittest.XmlInputTest
 */
class XmlFile extends Address implements Value {
  private $file;

  /**
   * Creates a new file-based XML input
   *
   * @param  string|io.Path|io.File $file
   */
  public function __construct($file) {
    $this->file= $file instanceof File ? $file : new File($file);
  }

  /** @return io.streams.InputStream */
  protected function stream() { return $this->file->in(); }

  /** @return string */
  public function toString() {
    return nameof($this).'<'.$this->file->toString().'>';
  }

  /** @return string */
  public function hashCode() {
    return 'F'.$this->file->hashCode();
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->file->compareTo($value->file) : 1;
  }
}