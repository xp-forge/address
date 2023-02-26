<?php namespace util\address;

/**
 * JSON streaming input
 *
 * @test  util.address.unittest.JsonInputTest
 * @test  util.address.unittest.StructureOfTest
 */
class JsonStreaming extends Streaming {

  /** Iterator implementation */
  public function iterator(): StreamIterator { return new JsonIterator($this->stream); }

}