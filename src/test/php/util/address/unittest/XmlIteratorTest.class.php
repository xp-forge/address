<?php namespace util\address\unittest;

use io\streams\{InputStream, MemoryInputStream};
use lang\IllegalStateException;
use unittest\Assert;
use unittest\{Test, Values};
use util\address\XmlIterator;

class XmlIteratorTest {

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
    Assert::equals($expected, $actual);
  }

  #[Test]
  public function can_create() {
    new XmlIterator(new MemoryInputStream('<doc/>'));
  }

  #[Test]
  public function empty_document() {
    $this->assertIterated(
      [['/' => null]],
      new XmlIterator(new MemoryInputStream('<doc/>'))
    );
  }

  #[Test]
  public function empty_document_with_declaration() {
    $this->assertIterated(
      [['/' => null]],
      new XmlIterator(new MemoryInputStream('<?xml version="1.0" encoding="utf-8"?><doc/>'))
    );
  }

  #[Test, Values(['<doc><test/></doc>', '<doc><test></test></doc>'])]
  public function single_empty_node($xml) {
    $this->assertIterated(
      [['/' => null], ['//test' => null]],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[Test]
  public function single_node() {
    $this->assertIterated(
      [['/' => null], ['//test' => 'Test']],
      new XmlIterator(new MemoryInputStream('<doc><test>Test</test></doc>'))
    );
  }

  #[Test]
  public function repeated_node() {
    $this->assertIterated(
      [['/' => null], ['//test' => 'a'], ['//test' => 'b']],
      new XmlIterator(new MemoryInputStream('<doc><test>a</test><test>b</test></doc>'))
    );
  }

  #[Test]
  public function nested_node() {
    $this->assertIterated(
      [['/' => null], ['//test' => null], ['//test/nested' => 'Test']],
      new XmlIterator(new MemoryInputStream('<doc><test><nested>Test</nested></test></doc>'))
    );
  }

  #[Test, Values(['<doc a="1" b="2" c="&gt;" d="&quot;" e="\'">', "<doc a='1' b='2' c='&gt;' d='\"' e='&apos;'>", '<doc a= "1" b= "2" c= "&gt;" d= "&quot;" e= "\'">', "<doc a= '1' b= '2' c= '&gt;' d= '\"' e= '&apos;'>", '<doc  a = "1"  b = "2"  c = "&gt;"  d = "&quot;"  e = "\'" >', "<doc  a = '1'  b = '2'  c = '&gt;'  d = '\"' e = '&apos;'>"])]
  public function attributes($xml) {
    $this->assertIterated(
      [['/' => null], ['//@a' => '1'], ['//@b' => '2'], ['//@c' => '>'], ['//@d' => '"'], ['//@e' => "'"]],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[Test, Values(['<test><!-- Empty --></test>', '<test><!----></test>', '<test><!-- --></test>', '<test><!-->--></test>', '<test><!--no need to escape <code> & such in comments--></test>'])]
  public function comments($xml) {
    $this->assertIterated(
      [['/' => null]],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[Test]
  public function embedded_comment() {
    $this->assertIterated(
      [['/' => 'AB']],
      new XmlIterator(new MemoryInputStream('<test>A<!-- Empty -->B</test>'))
    );
  }

  #[Test]
  public function xml_entities() {
    $this->assertIterated(
      [['/' => '&<>"\'']],
      new XmlIterator(new MemoryInputStream('<chars>&amp;&lt;&gt;&quot;&apos;</chars>'))
    );
  }

  #[Test]
  public function entities_from_doctype() {
    $this->assertIterated(
      [['/' => 'Binford 6100 Tools - Copyright 2021'], ['//@power' => '6100'], ['//@price' => '.99 €']],
      new XmlIterator(new MemoryInputStream('
        <!DOCTYPE binford [
          <!ENTITY euro   "&#8364;">
          <!ENTITY prefix "Binford &more;">
          <!ENTITY more   "6100">
          <!ENTITY copy   "Copyright">
        ]>
        <binford power="&more;" price=".99 &euro;">&prefix; Tools - &copy; 2021</binford>
      '))
    );
  }

  #[Test]
  public function does_not_choke_on_recursion() {
    $this->assertIterated(
      [['/' => 'Jo Smith user@user.com Jo Smith &email;']],
      new XmlIterator(new MemoryInputStream('
        <!DOCTYPE recursion [
          <!ENTITY email "user@user.com &js;">
          <!ENTITY js "Jo Smith &email;">
        ]>
        <recursion>&js;</recursion>
      '))
    );
  }

  #[Test, Values(['<char>&#xDC;</char>', '<char>&#xdc;</char>', '<char>&#220;</char>'])]
  public function numeric_entity_handled($xml) {
    $this->assertIterated(
      [['/' => 'Ü']],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[Test]
  public function cdata_section() {
    $this->assertIterated(
      [['/' => null], ['//amp' => '&'], ['//lt' => '<'], ['//gt' => '>']],
      new XmlIterator(new MemoryInputStream('<doc><amp><![CDATA[&]]></amp><lt><![CDATA[<]]></lt><gt><![CDATA[>]]></gt></doc>'))
    );
  }

  #[Test]
  public function cdata_section_with_gt_embedded() {
    $this->assertIterated(
      [['/' => '2 > 1, 100 >> 1']],
      new XmlIterator(new MemoryInputStream('<doc><![CDATA[2 > 1, 100 >> 1]]></doc>'))
    );
  }

  #[Test, Values(['<doc><a></a><b>Test</b><c>Te st</c></doc>', '<doc><a> </a><b> Test </b><c> Te st </c></doc>', '<doc> <a> </a> <b> Test </b> <c> Te st </c> </doc>', '<doc> <a> </a> <b> Test </b> <c> Te&#x20;st </c> </doc>'])]
  public function surrounding_whitespace_is_irrelevant($xml) {
    $this->assertIterated(
      [['/' => null], ['//a' => null], ['//b' => 'Test'], ['//c' => 'Te st']],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }

  #[Test]
  public function whitespace_in_cdata_is_preserved() {
    $this->assertIterated(
      [['/' => ' ']],
      new XmlIterator(new MemoryInputStream('<doc><![CDATA[ ]]></doc>'))
    );
  }

  #[Test, Values([['', "<char>\303\234</char>"], ['', "<char><![CDATA[\303\234]]></char>"], ['encoding="utf-8"', "<char>\303\234</char>"], ['encoding="utf-8"', "<char><![CDATA[\303\234]]></char>"], ['encoding="iso-8859-1"', "<char>\334</char>"], ['encoding="iso-8859-1"', "<char><![CDATA[\334]]></char>"]])]
  public function encoding_handled($encoding, $xml) {
    $this->assertIterated(
      [['/' => 'Ü']],
      new XmlIterator(new MemoryInputStream('<?xml version="1.0" '.$encoding.'?>'.$xml))
    );
  }

  #[Test]
  public function international_use() {
    $this->assertIterated(
      [['/' => null], ['//俄语' => 'данные'], ['//俄语/@լեզու' => 'ռուսերեն']],
      new XmlIterator(new MemoryInputStream('<doc><俄语 լեզու="ռուսերեն">данные</俄语></doc>'))
    );
  }

  #[Test]
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

  #[Test]
  public function iterated_twice_with_rewindable_stream() {
    $it= new XmlIterator(new MemoryInputStream('<doc><verified/></doc>'));
    $expected= [['/' => null], ['//verified' => null]];
    $this->assertIterated($expected, $it);
    $this->assertIterated($expected, $it);
  }

  #[Test]
  public function cannot_rewind_non_seekable() {
    $it= new XmlIterator(new class() implements InputStream {
      public function available() { return false; }
      public function read($bytes= 8192) { return null; }
      public function close() { }
    });
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

  #[Test, Values(['<doc><test><it>worked', '<doc><test><it>worked</it>', '<doc><test><it>worked</it></test>'])]
  public function works_even_when_closing_tag_is_missing($xml) {
    $this->assertIterated(
      [['/' => null], ['//test' => null], ['//test/it' => 'worked']],
      new XmlIterator(new MemoryInputStream($xml))
    );
  }
}