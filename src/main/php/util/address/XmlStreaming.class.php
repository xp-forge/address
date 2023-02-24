<?php namespace util\address;

use Iterator;
use io\Channel;
use io\streams\{InputStream, MemoryInputStream};
use lang\{Closeable, Value};
use util\Objects;

/**
 * XML streaming input
 *
 * @test  util.address.unittest.XmlStreamingTest
 * @test  util.address.unittest.XmlInputTest
 */
class XmlStreaming extends Address implements Closeable, Value {
  private $stream;

  /** @param string|io.Channel|io.streams.InputStream $source */
  public function __construct($source) {
    if ($source instanceof InputStream) {
      $this->stream= $source;
    } else if ($source instanceof Channel) {
      $this->stream= $source->in();
    } else {
      $this->stream= new MemoryInputStream($source);
    }
  }

  /** Iterator implementation */
  protected function iterator(): Iterator { return new XmlIterator($this->stream); }

  /** @return string */
  public function toString() {

    // Most InputStream instances have a toString() method but do not implement
    // the Value interface, see https://github.com/xp-framework/core/issues/310
    if ($this->stream instanceof Value || method_exists($this->stream, 'toString')) {
      return nameof($this).'<'.$this->stream->toString().'>';
    } else {
      return nameof($this).'<'.Objects::stringOf($this->stream).'>';
    }
  }

  /** @return string */
  public function hashCode() {
    return 'XS'.Objects::hashOf($this->stream);
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->stream, $value->stream) : 1;
  }

  /** @return void */
  public function close() { $this->stream->close(); }
}