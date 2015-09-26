<?php namespace util\address\unittest;

use util\address\Definition;
use util\address\XmlString;
use util\address\ArrayOf;
use util\address\CreationOf;
use util\address\Enclosing;

class AddressTest extends \unittest\TestCase {

  #[@test]
  public function path() {
    $address= new XmlString('<doc/>');
    $this->assertEquals('/', $address->path());
  }

  #[@test]
  public function path_after_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $this->assertNull($address->path());
  }

  #[@test]
  public function empty_node_value() {
    $address= new XmlString('<doc/>');
    $this->assertNull($address->next());
  }

  #[@test]
  public function value_for_node_with_content() {
    $address= new XmlString('<doc>Test</doc>');
    $this->assertEquals('Test', $address->next());
  }

  #[@test, @expect('util.NoSuchElementException')]
  public function iteration_across_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $address->next();
  }

  #[@test]
  public function next_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    $this->assertEquals('Test', $address->next());
  }

  #[@test]
  public function next_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    $this->assertEquals('Test', $address->next());
  }

  #[@test]
  public function valid() {
    $address= new XmlString('<doc>Test</doc>');
    $this->assertTrue($address->valid());
  }

  #[@test]
  public function valid_after_end() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $this->assertFalse($address->valid());
  }

  #[@test]
  public function valid_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    $this->assertTrue($address->valid());
  }

  #[@test]
  public function valid_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    $this->assertTrue($address->valid());
  }

  #[@test]
  public function iteration() {
    $address= new XmlString('<doc><nested>Test</nested></doc>');
    $actual= [];
    while ($address->valid()) {
      $actual[]= [$address->path() => $address->next()];
    }
    $this->assertEquals([['/' => null], ['//nested' => 'Test']], $actual);
  }

  #[@test]
  public function next_with_definition() {
    $address= new XmlString('<doc><nested>Test</nested></doc>');
    $value= $address->next(new Enclosing('/'))->next(newinstance(Definition::class, [], [
      'create' => function($iteration) { return [$iteration->path() => $iteration->next()]; }
    ]));
    $this->assertEquals(['//nested' => 'Test'], $value);
  }

  #[@test]
  public function iteration_support() {
    $address= new XmlString('<doc><a>A</a><b>B</b></doc>');
    $this->assertEquals(['/' => null, '//a' => 'A', '//b' => 'B'], iterator_to_array($address));
  }

  #[@test]
  public function iteration_support_for_empty_document() {
    $address= new XmlString('<doc/>');
    $this->assertEquals(['/' => null], iterator_to_array($address));
  }
}