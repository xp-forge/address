<?php namespace util\address;

/**
 * XML streaming input
 *
 * @test  util.address.unittest.XmlStreamingTest
 * @test  util.address.unittest.XmlInputTest
 */
class XmlStreaming extends Streaming {

  /** Iterator implementation */
  public function iterator(): StreamIterator { return new XmlIterator($this->stream); }

}