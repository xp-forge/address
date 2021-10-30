<?php namespace util\address\unittest;

use unittest\{Assert, Test};
use util\address\{ArrayOf, ObjectOf, Enclosing, XmlString};

class ArrayOfTest {

  #[Test]
  public function array_definition() {
    $address= new XmlString('<books><book>Book #1</book><book>Book #2</book></books>');
    Assert::equals(['Book #1', 'Book #2'], $address->next(new Enclosing('/'))->next(new ArrayOf(null)));
  }

  #[Test]
  public function array_definition_with_base() {
    $address= new XmlString('<books><book>Book #1</book><book>Book #2</book></books>');
    Assert::equals(['Book #1', 'Book #2'], $address->next(new Enclosing('/'))->next(new ArrayOf(null, ['book'])));
  }

  #[Test]
  public function array_definition_with_multiple_bases() {
    $address= new XmlString('<tests><unit>a</unit><unit>b</unit><integration>c</integration></tests>');
    Assert::equals(['a', 'b', 'c'], $address->next(new Enclosing('/'))->next(new ArrayOf(null, ['unit', 'integration'])));
  }

  #[Test]
  public function array_of_books_definition() {
    $address= new XmlString('<books><book><name>Book #1</name></book><book><name>Book #2</name></book></books>');
    Assert::equals([new Book('Book #1'), new Book('Book #2')], $address->next(new Enclosing('/'))->next(new ArrayOf(new BookDefinition())));
  }

  #[Test]
  public function array_of_books_definition_using_compact_form() {
    $address= new XmlString('<books><book>Book #1</book><book>Book #2</book></books>');
    Assert::equals(
      [new Book('Book #1'), new Book('Book #2')],
      $address->next(new Enclosing('/'))->next(new ArrayOf(new ObjectOf(Book::class, [
        '.' => function($iteration) { $this->name= $iteration->next(); }
      ])))
    );
  }

  #[Test]
  public function array_of_books_definition_using_compact_form_and_attributes() {
    $address= new XmlString('<books><book author="Test">Book #1</book><book>Book #2</book></books>');
    Assert::equals(
      [new Book('Book #1', new Author('Test')), new Book('Book #2')],
      $address->next(new Enclosing('/'))->next(new ArrayOf(new ObjectOf(Book::class, [
        '.'       => function($iteration) { $this->name= $iteration->next(); },
        '@author' => function($iteration) { $this->author= new Author($iteration->next()); }
      ])))
    );
  }
}