<?php namespace util\address\unittest;

use io\streams\{InputStream, MemoryInputStream};
use lang\IllegalStateException;
use util\address\XmlIterator;

class XmlIteratorTest extends \unittest\TestCase {

  /**
   * Assert iteration result
   *
   * @param  [:var][] $expected
   * @param  util.data.XmlIterator $fixture
   */
  protected function assertIterated($expected, XmlIterator $fixture) {
    $actual= [];
    foreach ($fixture as $key => $value) {
      $actual[]= [$key => $value];
    }
    $this->assertEquals($expected, $actual);
  }

  #[@test]
  public function can_create() {
    new XmlIterator(new MemoryInputStream('<doc/>'));
  }

  #[@test]
  public function empty_document() {
    $this->assertIterated(
      [['/' => null]],
      new XmlIterator(new MemoryInputStream('<doc/>'))
    );
  }

  #[@test]
  public function empty_document_with_declaration() {
    $this->assertIterated(
      [['/' => null]],
      new XmlIterator(new MemoryInputStream('<?xml version="1.0" encoding="utf-8"?><doc/>'))
    );
  }

  #[@test, @values([
  #  '<doc><test/></doc>',
  #  '<doc><test></test></doc>'
  #])]
  public function single_empty_node($xml) {
    $this->assertIterated(
      [['/' => null], ['//test' => null]],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[@test]
  public function single_node() {
    $this->assertIterated(
      [['/' => null], ['//test' => 'Test']],
      new XmlIterator(new MemoryInputStream('<doc><test>Test</test></doc>'))
    );
  }

  #[@test]
  public function repeated_node() {
    $this->assertIterated(
      [['/' => null], ['//test' => 'a'], ['//test' => 'b']],
      new XmlIterator(new MemoryInputStream('<doc><test>a</test><test>b</test></doc>'))
    );
  }

  #[@test]
  public function nested_node() {
    $this->assertIterated(
      [['/' => null], ['//test' => null], ['//test/nested' => 'Test']],
      new XmlIterator(new MemoryInputStream('<doc><test><nested>Test</nested></test></doc>'))
    );
  }

  #[@test, @values([
  #  '<doc a="1" b="2" c="&gt;" d="&quot;" e="\'">',
  #  "<doc a='1' b='2' c='&gt;' d='\"' e='&apos;'>",
  #  '<doc a= "1" b= "2" c= "&gt;" d= "&quot;" e= "\'">',
  #  "<doc a= '1' b= '2' c= '&gt;' d= '\"' e= '&apos;'>",
  #  '<doc  a = "1"  b = "2"  c = "&gt;"  d = "&quot;"  e = "\'" >',
  #  "<doc  a = '1'  b = '2'  c = '&gt;'  d = '\"' e = '&apos;'>"
  #])]
  public function attributes($xml) {
    $this->assertIterated(
      [['/' => null], ['//@a' => '1'], ['//@b' => '2'], ['//@c' => '>'], ['//@d' => '"'], ['//@e' => "'"]],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[@test, @values([
  #  '<test><!-- Empty --></test>',
  #  '<test><!----></test>',
  #  '<test><!-- --></test>',
  #  '<test><!-->--></test>',
  #  '<test><!--no need to escape <code> & such in comments--></test>'
  #])]
  public function comments($xml) {
    $this->assertIterated(
      [['/' => null]],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[@test]
  public function embedded_comment() {
    $this->assertIterated(
      [['/' => 'AB']],
      new XmlIterator(new MemoryInputStream('<test>A<!-- Empty -->B</test>'))
    );
  }

  #[@test]
  public function xml_entities() {
    $this->assertIterated(
      [['/' => '&<>"\'']],
      new XmlIterator(new MemoryInputStream('<chars>&amp;&lt;&gt;&quot;&apos;</chars>'))
    );
  }

  #[@test, @values(['<char>&#xDC;</char>', '<char>&#xdc;</char>', '<char>&#220;</char>'])]
  public function numeric_entity_handled($xml) {
    $this->assertIterated(
      [['/' => 'Ü']],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[@test]
  public function cdata_section() {
    $this->assertIterated(
      [['/' => null], ['//amp' => '&'], ['//lt' => '<'], ['//gt' => '>']],
      new XmlIterator(new MemoryInputStream('<doc><amp><![CDATA[&]]></amp><lt><![CDATA[<]]></lt><gt><![CDATA[>]]></gt></doc>'))
    );
  }

  #[@test]
  public function cdata_section_with_gt_embedded() {
    $this->assertIterated(
      [['/' => '2 > 1, 100 >> 1']],
      new XmlIterator(new MemoryInputStream('<doc><![CDATA[2 > 1, 100 >> 1]]></doc>'))
    );
  }

  #[@test, @values([
  #  '<doc><a></a><b>Test</b><c>Te st</c></doc>',
  #  '<doc><a> </a><b> Test </b><c> Te st </c></doc>',
  #  '<doc> <a> </a> <b> Test </b> <c> Te st </c> </doc>',
  #  '<doc> <a> </a> <b> Test </b> <c> Te&#x20;st </c> </doc>'
  #])]
  public function surrounding_whitespace_is_irrelevant($xml) {
    $this->assertIterated(
      [['/' => null], ['//a' => null], ['//b' => 'Test'], ['//c' => 'Te st']],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[@test]
  public function whitespace_in_cdata_is_preserved() {
    $this->assertIterated(
      [['/' => ' ']],
      new XmlIterator(new MemoryInputStream('<doc><![CDATA[ ]]></doc>'))
    );
  }

  #[@test, @values([
  #  ['', "<char>\303\234</char>"],
  #  ['', "<char><![CDATA[\303\234]]></char>"],
  #  ['encoding="utf-8"', "<char>\303\234</char>"],
  #  ['encoding="utf-8"', "<char><![CDATA[\303\234]]></char>"],
  #  ['encoding="iso-8859-1"', "<char>\334</char>"],
  #  ['encoding="iso-8859-1"', "<char><![CDATA[\334]]></char>"]
  #])]
  public function encoding_handled($encoding, $xml) {
    $this->assertIterated(
      [['/' => 'Ü']],
      new XmlIterator(new MemoryInputStream('<?xml version="1.0" '.$encoding.'?>'.$xml))
    );
  }

  #[@test]
  public function international_use() {
    $this->assertIterated(
      [['/' => null], ['//俄语' => 'данные'], ['//俄语/@լեզու' => 'ռուսերեն']],
      new XmlIterator(new MemoryInputStream('<doc><俄语 լեզու="ռուսերեն">данные</俄语></doc>'))
    );
  }

  #[@test]
  public function book_example() {
    $this->assertIterated(
      [
        ['/' => null],
        ['//author' => null],
        ['//author/name' => null],
        ['//name' => 'Book']
      ],
      new XmlIterator(new MemoryInputStream('<book><author><name/></author><name>Book</name></book>'))
    );
  }

  #[@test]
  public function iterated_twice_with_rewindable_stream() {
    $it= new XmlIterator(new MemoryInputStream('<doc><verified/></doc>'));
    $expected= [['/' => null], ['//verified' => null]];
    $this->assertIterated($expected, $it);
    $this->assertIterated($expected, $it);
  }

  #[@test]
  public function cannot_rewind_non_seekable() {
    $it= new XmlIterator(newinstance(InputStream::class, [], [
      'available' => function() { return false; },
      'read'      => function($bytes= 8192) { return null; },
      'close'     => function() { }
    ]));
    $this->assertIterated([], $it);

    try {
      foreach ($it as $element) {
        $this->fail('Unreachable');
      }
      $this->fail('Expected exception not caught', null, 'lang.IllegalStateException');
    } catch (IllegalStateException $expected) {
      // OK
    }
  }

  #[@test, @values([
  #  '<doc><test><it>worked',
  #  '<doc><test><it>worked</it>',
  #  '<doc><test><it>worked</it></test>'
  #])]
  public function works_even_when_closing_tag_is_missing($xml) {
    $this->assertIterated(
      [['/' => null], ['//test' => null], ['//test/it' => 'worked']],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }
}