<?php namespace util\address\unittest;

use unittest\{Expect, Test};
use util\address\{ArrayOf, CreationOf, Definition, Enclosing, XmlString};

class AddressTest extends \unittest\TestCase {

  #[Test]
  public function path() {
    $address= new XmlString('<doc/>');
    $this->assertEquals('/', $address->path());
  }

  #[Test]
  public function path_after_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $this->assertNull($address->path());
  }

  #[Test]
  public function empty_node_value() {
    $address= new XmlString('<doc/>');
    $this->assertNull($address->next());
  }

  #[Test]
  public function value_for_node_with_content() {
    $address= new XmlString('<doc>Test</doc>');
    $this->assertEquals('Test', $address->next());
  }

  #[Test, Expect('util.NoSuchElementException')]
  public function iteration_across_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $address->next();
  }

  #[Test]
  public function next_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    $this->assertEquals('Test', $address->next());
  }

  #[Test]
  public function next_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    $this->assertEquals('Test', $address->next());
  }

  #[Test]
  public function valid() {
    $address= new XmlString('<doc>Test</doc>');
    $this->assertTrue($address->valid());
  }

  #[Test]
  public function valid_after_end() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $this->assertFalse($address->valid());
  }

  #[Test]
  public function valid_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    $this->assertTrue($address->valid());
  }

  #[Test]
  public function valid_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    $this->assertTrue($address->valid());
  }

  #[Test]
  public function iteration() {
    $address= new XmlString('<doc><nested>Test</nested></doc>');
    $actual= [];
    while ($address->valid()) {
      $actual[]= [$address->path() => $address->next()];
    }
    $this->assertEquals([['/' => null], ['//nested' => 'Test']], $actual);
  }

  #[Test]
  public function next_with_definition() {
    $address= new XmlString('<doc><nested>Test</nested></doc>');
    $value= $address->next(new Enclosing('/'))->next(new class() implements Definition {
      public function create($iteration) { return [$iteration->path() => $iteration->next()]; }
    });
    $this->assertEquals(['//nested' => 'Test'], $value);
  }

  #[Test]
  public function iteration_support() {
    $address= new XmlString('<doc><a>A</a><b>B</b></doc>');
    $this->assertEquals(['/' => null, '//a' => 'A', '//b' => 'B'], iterator_to_array($address));
  }

  #[Test]
  public function iteration_support_for_empty_document() {
    $address= new XmlString('<doc/>');
    $this->assertEquals(['/' => null], iterator_to_array($address));
  }
}