<?php namespace util\address\unittest;

use lang\XPClass;
use unittest\Assert;
use unittest\{Test, Values};
use util\address\{ObjectOf, XmlString};

class ObjectOfTest {

  /** @return var[][] */
  protected function bookTypes() {
    return [
      ['util.address.unittest.Book'],
      [XPClass::forName('util.address.unittest.Book')],
    ];
  }

  #[Test, Values('bookTypes')]
  public function compact_form($type) {
    $address= new XmlString('<book>Name</book>');
    Assert::equals(
      new Book('Name'),
      $address->next(new ObjectOf($type, [
        '.' => function($self, $it) { $self->name= $it->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node($type) {
    $address= new XmlString('<book><name>Name</name></book>');
    Assert::equals(
      new Book('Name'),
      $address->next(new ObjectOf($type, [
        'name'    => function($self, $it) { $self->name= $it->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node_and_attributes($type) {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      new Book('Name', new Author('Test')),
      $address->next(new ObjectOf($type, [
        'name'    => function($self, $it) { $self->name= $it->next(); },
        '@author' => function($self, $it) { $self->author= new Author($it->next()); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function any_child($type) {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      new Book('Name', new Author('Test')),
      $address->next(new ObjectOf($type, [
        '@author' => function($self, $it) { $self->author= new Author($it->next()); },
        '*'       => function($self, $it, $name) { $self->$name= $it->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function any_attribute($type) {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      new Book('Name', new Author('Test')),
      $address->next(new ObjectOf($type, [
        '@*'   => function($self, $it, $name) { $self->{$name}= new Author($it->next()); },
        'name' => function($self, $it) { $self->name= $it->next(); }
      ]))
    );
  }

  #[Test, Values(['<book><name>Name</name><author><name/></author></book>', '<book><author><name/></author><name>Name</name></book>', '<book><name>Name</name><author><name>Test</name></author></book>', '<book><author><name>Test</name></author><name>Name</name></book>'])]
  public function child_node_ordering($xml) {
    $address= new XmlString($xml);
    Assert::equals(new Book('Name', new Author('Test')), $address->next(new BookDefinition()));
  }

  #[Test, Values('bookTypes')]
  public function deprecated_form($type) {
    $definition= new ObjectOf($type, ['.' => function($it) { $this->name= $it->next(); }]);
    \xp::gc();

    $address= new XmlString('<book>Name</book>');
    Assert::equals(new Book('Name'), $address->next($definition));
  }
}