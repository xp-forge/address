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
        '.' => function(&$self, $it) { $self['name']= $it->next(); }
      ]))
    );
  }

  #[Test]
  public function child_node() {
    $address= new XmlString('<book><name>Name</name></book>');
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new MapOf([
        'name'    => function(&$self, $it) { $self['name']= $it->next(); }
      ]))
    );
  }

  #[Test]
  public function child_node_and_attributes() {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new MapOf([
        '@author' => function(&$self, $it) { $self['author']= $it->next(); },
        'name'    => function(&$self, $it) { $self['name']= $it->next(); }
      ]))
    );
  }

  #[Test]
  public function any_child() {
    $address= new XmlString('<book><name>Name</name><author>Test</author></book>');
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new MapOf([
        '*' => function(&$self, $it, $node) { $self[$node]= $it->next(); }
      ]))
    );
  }

  #[Test]
  public function any_attribute() {
    $address= new XmlString('<book asin="B01N1UPZ10" author="Test">Name</book>');
    Assert::equals(
      ['name' => 'Name', '@asin' => 'B01N1UPZ10', '@author' => 'Test'],
      $address->next(new MapOf([
        '@*' => function(&$self, $it, $attr) { $self[$attr]= $it->next(); },
        '.'  => function(&$self, $it) { $self['name']= $it->next(); }
      ]))
    );
  }
}