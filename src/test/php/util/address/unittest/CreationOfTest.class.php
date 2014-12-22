<?php namespace util\address\unittest;

use util\address\XmlString;
use util\address\CreationOf;

class CreationOfTest extends \unittest\TestCase {

  #[@test]
  public function compact_form() {
    $address= new XmlString('<book>Name</book>');
    $this->assertEquals(
      new Book('Name'),
      $address->next(new CreationOf('util.address.unittest.Book', [
        '.' => function($iteration) { $this->name= $iteration->next(); }
      ]))
    );
  }

  #[@test]
  public function child_node() {
    $address= new XmlString('<book><name>Name</name></book>');
    $this->assertEquals(
      new Book('Name'),
      $address->next(new CreationOf('util.address.unittest.Book', [
        'name'    => function($iteration) { $this->name= $iteration->next(); }
      ]))
    );
  }

  #[@test]
  public function child_node_and_attributes() {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    $this->assertEquals(
      new Book('Name', new Author('Test')),
      $address->next(new CreationOf('util.address.unittest.Book', [
        'name'    => function($iteration) { $this->name= $iteration->next(); },
        '@author' => function($iteration) { $this->author= new Author($iteration->next()); }
      ]))
    );
  }

  #[@test, @values([
  #  '<book><name>Name</name><author><name/></author></book>',
  #  '<book><author><name/></author><name>Name</name></book>',
  #  '<book><name>Name</name><author><name>Test</name></author></book>',
  #  '<book><author><name>Test</name></author><name>Name</name></book>'
  #])]
  public function by_definition($xml) {
    $address= new XmlString($xml);
    $this->assertEquals(new Book('Name', new Author('Test')), $address->next(new BookDefinition()));
  }
}