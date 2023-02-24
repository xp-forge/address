<?php namespace util\address;

use Iterator;

/**
 * XML stream input
 *
 * @test  util.address.unittest.XmlStreamTest
 * @test  util.address.unittest.XmlInputTest
 * @deprecated by XmlStreaming
 */
class XmlStream extends Address {

  /** Iterator implementation */
  protected function iterator(): Iterator { return new XmlIterator($this->stream); }

}