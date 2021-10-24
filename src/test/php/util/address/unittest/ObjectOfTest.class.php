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
}