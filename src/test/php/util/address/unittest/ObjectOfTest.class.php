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
        '.' => function($iteration) { $this->name= $iteration->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node($type) {
    $address= new XmlString('<book><name>Name</name></book>');
    Assert::equals(
      new Book('Name'),
      $address->next(new ObjectOf($type, [
        'name'    => function($iteration) { $this->name= $iteration->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node_and_attributes($type) {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      new Book('Name', new Author('Test')),
      $address->next(new ObjectOf($type, [
        'name'    => function($iteration) { $this->name= $iteration->next(); },
        '@author' => function($iteration) { $this->author= new Author($iteration->next()); }
      ]))
    );
  }

  #[Test, Values(['<book><name>Name</name><author><name/></author></book>', '<book><author><name/></author><name>Name</name></book>', '<book><name>Name</name><author><name>Test</name></author></book>', '<book><author><name>Test</name></author><name>Name</name></book>'])]
  public function child_node_ordering($xml) {
    $address= new XmlString($xml);
    Assert::equals(new Book('Name', new Author('Test')), $address->next(new ObjectOf(Book::class, [
      'name'   => function($iteration) { $this->name= $iteration->next(); },
      'author' => function($iteration) { $this->author= $iteration->next(new ObjectOf(Author::class, [
        'name'   => function($iteration) { $this->name= $iteration->next() ?? 'Test'; }
      ])); }
    ])));
  }
}