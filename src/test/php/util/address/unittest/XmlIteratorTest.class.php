<?php namespace util\address\unittest;

use io\streams\{InputStream, MemoryInputStream};
use lang\{FormatException, IllegalStateException};
use test\{Assert, Expect, Test, Values};
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

  #[Test, Values(['A ', ' A', ' A '])]
  public function whitespace($content) {
    $this->assertIterated(
      [['/' => 'A']],
      new XmlIterator(new MemoryInputStream("<test>{$content}</test>"))
    );
  }

  #[Test, Values(['A ', ' A', ' A '])]
  public function preserve_whitespace($content) {
    $this->assertIterated(
      [['/' => $content]],
      new XmlIterator(new MemoryInputStream("<test xml:space=\"preserve\">{$content}</test>"))
    );
  }

  #[Test]
  public function entities_from_doctype() {
    $this->assertIterated(
      [['/' => 'Binford 6100 Tools - Copyright 2021'], ['//@power' => '6100'], ['//@price' => '.99 €']],
      new XmlIterator(new MemoryInputStream('
        <!DOCTYPE binford [
          <!ENTITY euro   "&#8364;">
          <!ENTITY tools  "&prefix; Tools">
          <!ENTITY prefix "Binford &more;">
          <!ENTITY more   "6100">
          <!ENTITY copy   "Copyright">
        ]>
        <binford power="&more;" price=".99 &euro;">&tools; - &copy; 2021</binford>
      '))
    );
  }

  #[Test]
  public function entities_are_case_sensitive() {
    $this->assertIterated(
      [['/' => 'lower and upper case']],
      new XmlIterator(new MemoryInputStream('
        <!DOCTYPE binford [
          <!ENTITY case "lower">
          <!ENTITY Case "upper">
        ]>
        <test>&case; and &Case; case</test>
      '))
    );
  }

  #[Test, Expect(class: FormatException::class, message: 'Entity &missing; not defined')]
  public function raises_error_for_missing_entities() {
    iterator_count(new XmlIterator(new MemoryInputStream('
      <!DOCTYPE test [
        <!ENTITY js "Jo Smith &missing;">
      ]>
      <test>&js;</test>
    ')));
  }

  #[Test, Expect(class: FormatException::class, message: 'Entity reference loop &js; > &js;')]
  public function does_not_choke_on_recursion() {
    iterator_count(new XmlIterator(new MemoryInputStream('
      <!DOCTYPE test [
        <!ENTITY js "Jo Smith &js;">
      ]>
      <test>&js;</test>
    ')));
  }

  #[Test, Expect(class: FormatException::class, message: 'Entity reference loop &js; > &address; > &js;')]
  public function does_not_choke_on_recursion_over_multiple_entities() {
    iterator_count(new XmlIterator(new MemoryInputStream('
      <!DOCTYPE test [
        <!ENTITY email "user@user.com">
        <!ENTITY address "&email; &js;">
        <!ENTITY js "Jo Smith &address;">
      ]>
      <test>&js;</test>
    ')));
  }

  #[Test]
  public function ignores_parameter_entities() {
    $this->assertIterated(
      [['/' => 'Jo Smith %param;']],
      new XmlIterator(new MemoryInputStream('
        <!DOCTYPE test [
          <!ENTITY js "Jo Smith %param;">
        ]>
        <test>&js;</test>
      '))
    );
  }

  #[Test, Values(['SYSTEM "copyright.xml"', 'PUBLIC "-//W3C//TEXT copyright//EN" "http://www.w3.org/xmlspec/copyright.xml"'])]
  public function ignores_external_entity($declaration) {
    $this->assertIterated(
      [['/' => '&c;']],
      new XmlIterator(new MemoryInputStream('
        <!DOCTYPE external [
          <!ENTITY c '.$declaration.'>
        ]>
        <external>&c;</external>
      '))
    );
  }

  #[Test, Expect(IllegalStateException::class), Values(['html SYSTEM "html.dtd"', 'HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"'])]
  public function does_not_support_external_dtds($declaration) {
    iterator_count(new XmlIterator(new MemoryInputStream('<!DOCTYPE '.$declaration.'><html>...</html>')));
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

  #[Test]
  public function cdata_section_including_end_tag() {
    $this->assertIterated(
      [['/' => 'End: ]]>']],
      new XmlIterator(new MemoryInputStream('<doc><![CDATA[End: ]]]]><![CDATA[>]]></doc>'))
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
  public function encoding_handled_in_content($encoding, $xml) {
    $this->assertIterated(
      [['/' => 'Ü']],
      new XmlIterator(new MemoryInputStream('<?xml version="1.0" '.$encoding.'?>'.$xml))
    );
  }

  #[Test, Values([['', "<char id='\303\234'/>"], ['encoding="utf-8"', "<char id='\303\234'/>"], ['encoding="iso-8859-1"', "<char id='\334'/>"]])]
  public function encoding_handled_in_attributes($encoding, $xml) {
    $this->assertIterated(
      [['/' => null], ['//@id' => 'Ü']],
      new XmlIterator(new MemoryInputStream('<?xml version="1.0" '.$encoding.'?>'.$xml))
    );
  }

  #[Test, Values([['', "\303\234"], ['encoding="utf-8"', "\303\234"], ['encoding="iso-8859-1"', "\334"]])]
  public function encoding_handled_in_doctype_entities($encoding, $cdata) {
    $this->assertIterated(
      [['/' => 'Ü']],
      new XmlIterator(new MemoryInputStream('
        <?xml version="1.0" '.$encoding.'?>
        <!DOCTYPE test [
          <!ENTITY Uuml "'.$cdata.'">
        ]>
        <test>&Uuml;</test>
      '))
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