<?php namespace util\address\unittest;

use lang\{IllegalStateException, Runnable};
use unittest\{Assert, Expect, Ignore, Test};
use util\NoSuchElementException;
use util\address\{ArrayOf, CreationOf, Definition, XmlString};

class AddressTest {

  /** @return util.address.Definition */
  private function asMap() {
    return new class() implements Definition {
      public function create($it) { return [$it->path() => $it->next()]; }
    };
  }

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
  public function next_after_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $address->next();
  }

  #[Test, Expect(NoSuchElementException::class)]
  public function value_after_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $address->value();
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
    Assert::equals(['/' => 'Test'], $address->next($this->asMap()));
  }

  #[Test]
  public function value_with_definition() {
    $address= new XmlString('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($this->asMap()));
  }

  #[Test]
  public function repeated_value_with_definition() {
    $definition= $this->asMap();

    $address= new XmlString('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($definition), '#1');
    Assert::equals(['/' => 'Test'], $address->value($definition), '#2');
  }

  #[Test]
  public function next_after_value_with_definition() {
    $definition= $this->asMap();

    $address= new XmlString('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($definition), '#1');
    Assert::equals(['/' => 'Test'], $address->next($definition), '#2');
  }

  #[Test]
  public function repeated_value_with_varying_definition() {
    $asValue= new class() implements Definition {
      public function create($it) { return $it->next(); }
    };

    $address= new XmlString('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($this->asMap()), '#1');
    Assert::equals('Test', $address->value($asValue), '#2');
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