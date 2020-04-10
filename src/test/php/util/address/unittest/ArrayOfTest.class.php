<?php namespace util\address\unittest;

use util\address\{ArrayOf, CreationOf, Enclosing, XmlString};

class ArrayOfTest extends \unittest\TestCase {

  #[@test]
  public function array_definition() {
    $address= new XmlString('<books><book>Book #1</book><book>Book #2</book></books>');
    $this->assertEquals(['Book #1', 'Book #2'], $address->next(new Enclosing('/'))->next(new ArrayOf(null)));
  }

  #[@test]
  public function array_definition_with_base() {
    $address= new XmlString('<tests><unit>a</unit><unit>b</unit><integration>c</integration></tests>');
    $this->assertEquals(['a', 'b', 'c'], $address->next(new Enclosing('/'))->next(new ArrayOf(null, ['unit', 'integration'])));
  }

  #[@test]
  public function array_of_books_definition() {
    $address= new XmlString('<books><book><name>Book #1</name></book><book><name>Book #2</name></book></books>');
    $this->assertEquals([new Book('Book #1'), new Book('Book #2')], $address->next(new Enclosing('/'))->next(new ArrayOf(new BookDefinition())));
  }

  #[@test]
  public function array_of_books_definition_using_compact_form() {
    $address= new XmlString('<books><book>Book #1</book><book>Book #2</book></books>');
    $this->assertEquals(
      [new Book('Book #1'), new Book('Book #2')],
      $address->next(new Enclosing('/'))->next(new ArrayOf(new CreationOf('util.address.unittest.Book', [
        '.' => function($iteration) { $this->name= $iteration->next(); }
      ])))
    );
  }

  #[@test]
  public function array_of_books_definition_using_compact_form_and_attributes() {
    $address= new XmlString('<books><book author="Test">Book #1</book><book>Book #2</book></books>');
    $this->assertEquals(
      [new Book('Book #1', new Author('Test')), new Book('Book #2')],
      $address->next(new Enclosing('/'))->next(new ArrayOf(new CreationOf('util.address.unittest.Book', [
        '.'       => function($iteration) { $this->name= $iteration->next(); },
        '@author' => function($iteration) { $this->author= new Author($iteration->next()); }
      ])))
    );
  }
}