<?php namespace util\address\unittest;

use unittest\{Assert, Test};
use util\Date;
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

  #[Test, Values(['<book><name>Name</name><author><name/></author></book>', '<book><author><name/></author><name>Name</name></book>', '<book><name>Name</name><author><name>Test</name></author></book>', '<book><author><name>Test</name></author><name>Name</name></book>'])]
  public function child_node_ordering_irrelevant($xml) {
    $address= new XmlString($xml);
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new MapOf([
        'name'        => function(&$self, $it) { $self['name']= $it->next(); },
        'author/name' => function(&$self, $it) { $self['author']= $it->next() ?? 'Test'; },
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
        '*' => function(&$self, $it, $name) { $self[$name]= $it->next(); }
      ]))
    );
  }

  #[Test]
  public function any_attribute() {
    $address= new XmlString('<book asin="B01N1UPZ10" author="Test">Name</book>');
    Assert::equals(
      ['name' => 'Name', 'asin' => 'B01N1UPZ10', 'author' => 'Test'],
      $address->next(new MapOf([
        '@*' => function(&$self, $it, $name) { $self[$name]= $it->next(); },
        '.'  => function(&$self, $it) { $self['name']= $it->next(); }
      ]))
    );
  }

  #[Test]
  public function can_initialize() {
    $address= new XmlString('<book asin="B01N1UPZ10">Name</book>');
    Assert::equals(
      ['name' => 'Name', 'asin' => 'B01N1UPZ10', 'authors' => []],
      $address->next(new MapOf([
        '.'  => function(&$self, $it) { $self= ['name' => $it->next(), 'authors' => []]; },
        '@*' => function(&$self, $it, $name) { $self[$name]= $it->next(); },
      ]))
    );
  }

  #[Test]
  public function can_use_subpaths() {
    $address= new XmlString('<book><name>Name</name><authors><name>A</name><name>B</name></authors></book>');
    Assert::equals(
      ['name' => 'Name', 'authors' => ['A', 'B']],
      $address->next(new MapOf([
        '.'            => function(&$self, $it) { $self['authors']= []; $it->next(); },
        'name'         => function(&$self, $it) { $self['name']= $it->next(); },
        'authors/name' => function(&$self, $it) { $self['authors'][]= $it->next(); },
      ]))
    );
  }

  #[Test]
  public function combine_values_during_finalization() {
    $address= new XmlString('<created><date>2022-10-31</date><time>16:26:53</time></created>');
    $values= [];
    Assert::equals(
      ['date' => new Date('2022-10-31 16:26:53')],
      $address->next(new MapOf([
        'date' => function(&$self, $it) use(&$values) { $values[0]= $it->next(); },
        'time' => function(&$self, $it) use(&$values) { $values[1]= $it->next(); },
        '/'    => function(&$self, $it) use(&$values) { $self['date']= new Date(implode(' ', $values)); },
      ]))
    );
  }
}