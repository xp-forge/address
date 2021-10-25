<?php namespace util\address\unittest;

use unittest\{Assert, Test};
use util\address\{MapOf, XmlString};

class MapOfTest {

  #[Test]
  public function compact_form() {
    $address= new XmlString('<book>Name</book>');
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new MapOf([
        '.' => function($it) { return ['name' => $it->next()]; }
      ]))
    );
  }

  #[Test]
  public function child_node() {
    $address= new XmlString('<book><name>Name</name></book>');
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new MapOf([
        'name'    => function($it) { return ['name' => $it->next()]; }
      ]))
    );
  }

  #[Test]
  public function child_node_and_attributes() {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new MapOf([
        '@author' => function($it) { return ['author' => $it->next()]; },
        'name'    => function($it) { return ['name' => $it->next()]; }
      ]))
    );
  }

  #[Test]
  public function star() {
    $address= new XmlString('<book><name>Name</name><author>Test</author></book>');
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new MapOf([
        '*' => function($it, $node) { return [$node => $it->next()]; }
      ]))
    );
  }
}