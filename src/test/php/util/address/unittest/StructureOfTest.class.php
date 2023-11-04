<?php namespace util\address\unittest;

use test\{Assert, Test};
use util\address\{StructureOf, JsonStreaming};

class StructureOfTest {

  #[Test]
  public function string() {
    $address= new JsonStreaming('"Test"');
    Assert::equals('Test', $address->next(new StructureOf()));
  }

  #[Test]
  public function list() {
    $address= new JsonStreaming('["red","green","blue"]"');
    Assert::equals(
      ['red', 'green', 'blue'],
      $address->next(new StructureOf())
    );
  }

  #[Test]
  public function object() {
    $address= new JsonStreaming('{"name":"Test","ok":true,"undefined":null}"');
    Assert::equals(
      ['name' => 'Test', 'ok' => true, 'undefined' => null],
      $address->next(new StructureOf())
    );
  }

  #[Test]
  public function object_containing_list() {
    $address= new JsonStreaming('{"colors":["red","green","blue"]}');
    Assert::equals(
      ['colors' => ['red', 'green', 'blue']],
      $address->next(new StructureOf())
    );
  }

  #[Test]
  public function containing_list_of_objects() {
    $address= new JsonStreaming('{"colors":[{"id":"green","component":"G"},{"id":"red","component":"R"}]}');
    Assert::equals(
      ['colors' => [
        ['id' => 'green', 'component' => 'G'],
        ['id' => 'red', 'component' => 'R'],
      ]],
      $address->next(new StructureOf())
    );
  }
}