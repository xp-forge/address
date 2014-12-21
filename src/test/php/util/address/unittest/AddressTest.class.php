<?php namespace util\address\unittest;

use util\address\XmlString;
use util\address\ArrayOf;
use util\address\CreationOf;
use util\address\Enclosing;

class AddressTest extends \unittest\TestCase {

  #[@test]
  public function path() {
    $address= new XmlString('<doc/>');
    $this->assertEquals('/', $address->path());
  }

  #[@test]
  public function path_after_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $this->assertNull($address->path());
  }

  #[@test]
  public function empty_node_value() {
    $address= new XmlString('<doc/>');
    $this->assertNull($address->next());
  }

  #[@test]
  public function value_for_node_with_content() {
    $address= new XmlString('<doc>Test</doc>');
    $this->assertEquals('Test', $address->next());
  }

  #[@test, @expect('util.NoSuchElementException')]
  public function iteration_across_end() {
    $address= new XmlString('<doc/>');
    $address->next();
    $address->next();
  }

  #[@test]
  public function next_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    $this->assertEquals('Test', $address->next());
  }

  #[@test]
  public function next_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    $this->assertEquals('Test', $address->next());
  }

  #[@test]
  public function valid() {
    $address= new XmlString('<doc>Test</doc>');
    $this->assertTrue($address->valid());
  }

  #[@test]
  public function valid_after_end() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $this->assertFalse($address->valid());
  }

  #[@test]
  public function valid_after_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->reset();
    $this->assertTrue($address->valid());
  }

  #[@test]
  public function valid_after_next_and_resetting() {
    $address= new XmlString('<doc>Test</doc>');
    $address->next();
    $address->reset();
    $this->assertTrue($address->valid());
  }

  #[@test]
  public function iteration() {
    $address= new XmlString('<doc><nested>Test</nested></doc>');
    $actual= [];
    while ($address->valid()) {
      $actual[]= [$address->path() => $address->next()];
    }
    $this->assertEquals([['/' => null], ['//nested' => 'Test']], $actual);
  }

  #[@test]
  public function next_with_definition() {
    $address= new XmlString('<doc><nested>Test</nested></doc>');
    $value= $address->next(new Enclosing('/'))->next(newinstance('util.address.Definition', [], [
      'create' => function($iteration) { return [$iteration->path() => $iteration->next()]; }
    ]));
    $this->assertEquals(['//nested' => 'Test'], $value);
  }

  #[@test, @values([
  #  '<book><name>Name</name><author><name/></author></book>',
  #  '<book><author><name/></author><name>Name</name></book>',
  #  '<book><name>Name</name><author><name>Test</name></author></book>',
  #  '<book><author><name>Test</name></author><name>Name</name></book>'
  #])]
  public function book_definition($xml) {
    $address= new XmlString($xml);
    $this->assertEquals(new Book('Name', new Author('Test')), $address->next(new BookDefinition()));
  }

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
}