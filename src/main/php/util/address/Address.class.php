<?php namespace util\address;

use Iterator;

/**
 * Base class for all XML inputs
 *
 * @deprecated Inherit from util.address.Streaming instead! 
 */
abstract class Address extends Streaming {

  /**
   * Returns a stream
   *
   * @return io.streams.InputStream
   */
  protected function stream() { return $this->stream; }

  /**
   * Creates an iterator. Default implementation is to return an
   * `XmlIterator` instance for BC reasons.
   */
  protected function iterator(): Iterator { return new XmlIterator($this->stream()); }

}