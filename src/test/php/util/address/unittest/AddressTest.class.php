<?php namespace util\address\unittest;

use unittest\{Assert, Expect, Test};
use util\NoSuchElementException;
use util\address\{ArrayOf, CreationOf, Definition, XmlString};

class AddressTest {

  #[Test]
  public function path() {
    $address= new XmlString('<doc/>');
    Assert::equals('/', $address->path());
  }

  #[Test]
  public function path_after_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    Assert::null($address->path());
  }

  #[Test]
  public function empty_node_value() {
    $address= new XmlString('<doc/>');
    Assert::null($address->next());
  }

  #[Test]
  public function value_for_node_with_content() {
    $address= new XmlString('<doc>Test</doc>');
    Assert::equals('Test', $address->next());
  }

  #[Test, Expect(NoSuchElementException::class)]
  public function iteration_across_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $address->next();
  }

  #[Test]
  public function next_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    Assert::equals('Test', $address->next());
  }

  #[Test]
  public function next_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    Assert::equals('Test', $address->next());
  }

  #[Test]
  public function value() {
    $address= new XmlString('<doc>Test</doc>');
    Assert::equals('Test', $address->value());
  }

  #[Test]
  public function valid() {
    $address= new XmlString('<doc>Test</doc>');
    Assert::true($address->valid());
  }

  #[Test]
  public function valid_after_end() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    Assert::false($address->valid());
  }

  #[Test]
  public function valid_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    Assert::true($address->valid());
  }

  #[Test]
  public function valid_after_value() {
    $address= new XmlString('<doc>Test</doc>');
    $address->value();
    Assert::true($address->valid());
  }

  #[Test]
  public function valid_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    Assert::true($address->valid());
  }

  #[Test]
  public function iteration() {
    $address= new XmlString('<doc><nested>Test</nested></doc>');
    $actual= [];
    while ($address->valid()) {
      $actual[]= [$address->path() => $address->next()];
    }
    Assert::equals([['/' => null], ['//nested' => 'Test']], $actual);
  }

  #[Test]
  public function next_with_definition() {
    $address= new XmlString('<doc>Test</doc>');
    $value= $address->next(new class() implements Definition {
      public function create($iteration) { return [$iteration->path() => $iteration->next()]; }
    });
    Assert::equals(['/' => 'Test'], $value);
  }

  #[Test]
  public function value_with_definition() {
    $address= new XmlString('<doc>Test</doc>');
    $value= $address->value(new class() implements Definition {
      public function create($iteration) { return [$iteration->path() => $iteration->next()]; }
    });
    Assert::equals(['/' => 'Test'], $value);
  }

  #[Test]
  public function iteration_support() {
    $address= new XmlString('<doc><a>A</a><b>B</b></doc>');
    Assert::equals(['/' => null, '//a' => 'A', '//b' => 'B'], iterator_to_array($address));
  }

  #[Test]
  public function iteration_support_for_empty_document() {
    $address= new XmlString('<doc/>');
    Assert::equals(['/' => null], iterator_to_array($address));
  }

  #[Test]
  public function iteration_valid_with_value() {
    $actual= [];
    $address= new XmlString('<doc><a>A</a><b>B</b></doc>');
    foreach ($address as $path => $value) {
      $actual[$path]= [$address->value(), $address->valid()];
    }
    Assert::equals(['/' => [null, true], '//a' => ['A', true], '//b' => ['B', true]], $actual);
  }

  #[Test]
  public function iteration_valid_with_value_and_definition() {
    $definition= new class() implements Definition {
      public function create($iteration) { return $iteration->next(); }
    };

    $actual= [];
    $address= new XmlString('<doc><a>A</a><b>B</b></doc>');
    foreach ($address as $path => $value) {
      $actual[$path]= [$address->value($definition), $address->valid()];
    }
    Assert::equals(['/' => [null, true], '//a' => ['A', true], '//b' => ['B', true]], $actual);
  }
}