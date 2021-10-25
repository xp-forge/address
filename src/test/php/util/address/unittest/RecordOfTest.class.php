<?php namespace util\address\unittest;

use lang\XPClass;
use unittest\Assert;
use unittest\{Test, Values};
use util\address\{RecordOf, XmlString};

class RecordOfTest {

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
      $address->next(new RecordOf($type, [
        '.' => function(&$arguments, $it) { $arguments['name']= $it->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node($type) {
    $address= new XmlString('<book><name>Name</name></book>');
    Assert::equals(
      new Book('Name'),
      $address->next(new RecordOf($type, [
        'name'    => function(&$arguments, $it) { $arguments['name']= $it->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node_and_attributes($type) {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      new Book('Name', new Author('Test')),
      $address->next(new RecordOf($type, [
        'name'    => function(&$arguments, $it) { $arguments['name']= $it->next(); },
        '@author' => function(&$arguments, $it) { $arguments['author']= new Author($it->next()); }
      ]))
    );
  }
}