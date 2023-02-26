<?php namespace util\address;

/**
 * JSON streaming input
 *
 * @test  util.address.unittest.JsonInputTest
 */
class JsonStreaming extends Streaming {

  /** Iterator implementation */
  public function iterator(): StreamIterator { return new JsonIterator($this->stream); }

}