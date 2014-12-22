<?php namespace util\address\unittest;

use util\address\XmlString;
use util\address\MapOf;
use util\address\ArrayOf;
use util\address\Enclosing;

class MapOfTest extends \unittest\TestCase {
  const NESTED = '<map><a>A</a><b>B</b><c>%s</c><d>D</d></map>';

  #[@test]
  public function flat_map() {
    $address= new XmlString('<map><a>A</a><b>B</b></map>');
    $this->assertEquals(['a' => 'A', 'b' => 'B'], $address->next(new Enclosing('/'))->next(new MapOf()));
  }

  #[@test]
  public function map_with_nested_map() {
    $address= new XmlString(sprintf(self::NESTED, '<key>value</key><color>green</color>'));
    $this->assertEquals(
      ['a' => 'A', 'b' => 'B', 'c' => ['key' => 'value', 'color' => 'green'], 'd' => 'D'],
      $address->next(new Enclosing('/'))->next(new MapOf(['c' => new MapOf(null)]))
    );
  }

  #[@test]
  public function map_with_nested_array() {
    $address= new XmlString(sprintf(self::NESTED, '<key>value 1</key><key>value 2</key>'));
    $this->assertEquals(
      ['a' => 'A', 'b' => 'B', 'c' => ['value 1', 'value 2'], 'd' => 'D'],
      $address->next(new Enclosing('/'))->next(new MapOf(['c' => new ArrayOf(null)]))
    );
  }

  #[@test]
  public function map_with_nested_object() {
    $address= new XmlString(sprintf(self::NESTED, '<book><name>Name</name><author><name>Test</name></author></book>'));
    $this->assertEquals(
      ['a' => 'A', 'b' => 'B', 'c' => new Book('Name', new Author('Test')), 'd' => 'D'],
      $address->next(new Enclosing('/'))->next(new MapOf(['c' => new BookDefinition()]))
    );
  }
}