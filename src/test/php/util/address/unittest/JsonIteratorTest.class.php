<?php namespace util\address\unittest;

use io\streams\{InputStream, MemoryInputStream};
use lang\{IllegalStateException, FormatException};
use test\{Assert, Test, Values};
use util\address\JsonIterator;

class JsonIteratorTest extends StreamIteratorTest {

  /** @return iterable */
  private function scalars() {
    yield ['"Test"', 'Test'];
    yield ['""', ''];
    yield ['" "', ' '];
    yield ['"\""', '"'];
    yield ['"Escape\\\\"', 'Escape\\'];
    yield ['"A \"quote\"."', 'A "quote".'];
    yield ['"Have \"Error: A\""', 'Have "Error: A"'];
    yield ['"A\nB"', "A\nB"];
    yield ['"1\u20ac"', '1€'];
    yield ['"测测"', '测测'];  // Chinese for "measurement"

    yield ['1', 1];
    yield ['+42', +42];
    yield ['-6100', -6100];

    yield ['1.5', 1.5];
    yield ['1.123e3', 1.123e3];
    yield ['1E2', 1E2];
    yield ['1E-2', 1E-2];
    yield ['-0.5', -0.5];
    yield ['+6.1', +6.1];

    yield ['true', true];
    yield ['false', false];
    yield ['null', null];
  }

  #[Test]
  public function can_create() {
    new JsonIterator(new MemoryInputStream('{}'));
  }

  #[Test, Values(from: 'scalars')]
  public function scalar($input, $expected) {
    $this->assertIterated([[$expected]], new JsonIterator(new MemoryInputStream($input)));
  }

  #[Test]
  public function empty_map() {
    $this->assertIterated(
      [['/' => null]],
      new JsonIterator(new MemoryInputStream('{}'))
    );
  }

  #[Test]
  public function single_pair() {
    $this->assertIterated(
      [['/' => null], ['//test' => 'Test']],
      new JsonIterator(new MemoryInputStream('{"test":"Test"}'))
    );
  }

  #[Test, Values(['{"color":"Green","price":12.99}', '{"color": "Green", "price": 12.99}'])]
  public function two_pairs($input) {
    $this->assertIterated(
      [['/' => null], ['//color' => 'Green'], ['//price' => 12.99]],
      new JsonIterator(new MemoryInputStream($input))
    );
  }

  #[Test, Values(from: 'scalars')]
  public function map_with_scalar($input, $expected) {
    $this->assertIterated(
      [['/' => null], ['//value' => $expected], ['//ok' => true]],
      new JsonIterator(new MemoryInputStream('{"value":'.$input.',"ok":true}'))
    );
  }

  #[Test]
  public function empty_list() {
    $this->assertIterated(
      [['/' => null]],
      new JsonIterator(new MemoryInputStream('[]'))
    );
  }

  #[Test]
  public function single_element() {
    $this->assertIterated(
      [['/' => null], ['//[]' => 'Test']],
      new JsonIterator(new MemoryInputStream('["Test"]'))
    );
  }

  #[Test, Values(['["Color","Price"]', '["Color", "Price"]'])]
  public function two_elements($input) {
    $this->assertIterated(
      [['/' => null], ['//[]' => 'Color'], ['//[]' => 'Price']],
      new JsonIterator(new MemoryInputStream($input))
    );
  }

  #[Test]
  public function map_containing_list() {
    $this->assertIterated(
      [['/' => null], ['//items' => null], ['//items/[]' => 'One'], ['//items/[]' => 'Two']],
      new JsonIterator(new MemoryInputStream('{"items":["One","Two"]}'))
    );
  }
}