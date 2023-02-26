<?php namespace util\address\unittest;

use test\Assert;
use util\address\StreamIterator;

abstract class StreamIteratorTest {

  /**
   * Assert iteration result
   *
   * @param  [:var][] $expected
   * @param  util.address.StreamIterator $fixture
   */
  protected function assertIterated($expected, StreamIterator $fixture) {
    $actual= [];
    foreach ($fixture as $key => $value) {
      $actual[]= [$key => $value];
    }
    Assert::equals($expected, $actual);
  }
}