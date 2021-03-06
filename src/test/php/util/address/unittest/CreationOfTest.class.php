<?php namespace util\address\unittest;

use lang\XPClass;
use unittest\{Test, Values};
use util\address\{CreationOf, XmlString};

class CreationOfTest extends \unittest\TestCase {

  /** @return var[][] */
  protected function bookTypes() {
    return [
      ['util.address.unittest.Book'],
      [XPClass::forName('util.address.unittest.Book')],
      [Book::with()]
    ];
  }

  #[Test, Values('bookTypes')]
  public function compact_form($type) {
    $address= new XmlString('<book>Name</book>');
    $this->assertEquals(
      new Book('Name'),
      $address->next(new CreationOf($type, [
        '.' => function($iteration) { $this->name= $iteration->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node($type) {
    $address= new XmlString('<book><name>Name</name></book>');
    $this->assertEquals(
      new Book('Name'),
      $address->next(new CreationOf($type, [
        'name'    => function($iteration) { $this->name= $iteration->next(); }
      ]))
    );
  }

  #[Test, Values('bookTypes')]
  public function child_node_and_attributes($type) {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    $this->assertEquals(
      new Book('Name', new Author('Test')),
      $address->next(new CreationOf($type, [
        'name'    => function($iteration) { $this->name= $iteration->next(); },
        '@author' => function($iteration) { $this->author= new Author($iteration->next()); }
      ]))
    );
  }

  #[Test, Values(['<book><name>Name</name><author><name/></author></book>', '<book><author><name/></author><name>Name</name></book>', '<book><name>Name</name><author><name>Test</name></author></book>', '<book><author><name>Test</name></author><name>Name</name></book>'])]
  public function by_definition($xml) {
    $address= new XmlString($xml);
    $this->assertEquals(new Book('Name', new Author('Test')), $address->next(new BookDefinition()));
  }
}