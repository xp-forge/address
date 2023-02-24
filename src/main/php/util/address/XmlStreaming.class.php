<?php namespace util\address;

use Iterator;

/**
 * XML streaming input
 *
 * @test  util.address.unittest.XmlStreamingTest
 * @test  util.address.unittest.XmlInputTest
 */
class XmlStreaming extends Streaming {

  /** Iterator implementation */
  public function iterator(): Iterator { return new XmlIterator($this->stream); }

}