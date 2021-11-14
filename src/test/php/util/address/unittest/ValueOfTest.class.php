<?php namespace util\address\unittest;

use unittest\{Assert, Test};
use util\Date;
use util\address\{ValueOf, XmlString};

class ValueOfTest {

  #[Test]
  public function compact_form() {
    $address= new XmlString('<book>Name</book>');
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new ValueOf([], [
        '.' => function(&$self) { $self['name']= yield; }
      ]))
    );
  }

  #[Test]
  public function child_node() {
    $address= new XmlString('<book><name>Name</name></book>');
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new ValueOf([], [
        'name'    => function(&$self) { $self['name']= yield; }
      ]))
    );
  }

  #[Test, Values(['<book><name>Name</name><author><name/></author></book>', '<book><author><name/></author><name>Name</name></book>', '<book><name>Name</name><author><name>Test</name></author></book>', '<book><author><name>Test</name></author><name>Name</name></book>'])]
  public function child_node_ordering_irrelevant($xml) {
    $address= new XmlString($xml);
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new ValueOf([], [
        'name'        => function(&$self) { $self['name']= yield; },
        'author/name' => function(&$self) { $self['author']= yield ?? 'Test'; },
      ]))
    );
  }

  #[Test]
  public function child_node_and_attributes() {
    $address= new XmlString('<book author="Test"><name>Name</name></book>');
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new ValueOf([], [
        '@author' => function(&$self) { $self['author']= yield; },
        'name'    => function(&$self) { $self['name']= yield; }
      ]))
    );
  }

  #[Test]
  public function uses_default_value() {
    $address= new XmlString('<book asin="B01N1UPZ10">Name</book>');
    Assert::equals(
      ['name' => 'Name', 'asin' => 'B01N1UPZ10', 'authors' => []],
      $address->next(new ValueOf(['authors' => []], [
        '.'     => function(&$self) { $self['name']= yield; },
        '@asin' => function(&$self) { $self['asin']= yield; },
      ]))
    );
  }

  #[Test]
  public function any_child() {
    $address= new XmlString('<book><name>Name</name><author>Test</author></book>');
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new ValueOf([], [
        '*' => function(&$self, $name) { $self[$name]= yield; }
      ]))
    );
  }

  #[Test]
  public function any_attribute() {
    $address= new XmlString('<book asin="B01N1UPZ10" author="Test">Name</book>');
    Assert::equals(
      ['name' => 'Name', 'asin' => 'B01N1UPZ10', 'author' => 'Test'],
      $address->next(new ValueOf([], [
        '@*' => function(&$self, $name) { $self[$name]= yield; },
        '.'  => function(&$self) { $self['name']= yield; }
      ]))
    );
  }

  #[Test]
  public function can_use_subpaths() {
    $address= new XmlString('<book><name>Name</name><authors><name>A</name><name>B</name></authors></book>');
    Assert::equals(
      ['name' => 'Name', 'authors' => ['A', 'B']],
      $address->next(new ValueOf(['authors' => []], [
        'name'         => function(&$self) { $self['name']= yield; },
        'authors/name' => function(&$self) { $self['authors'][]= yield; },
      ]))
    );
  }

  #[Test]
  public function can_produce_arrays() {
    $address= new XmlString('<books><book>Book #1</book><book>Book #2</book></books>');
    Assert::equals(
      ['Book #1', 'Book #2'],
      $address->next(new ValueOf([], [
        'book' => function(&$self) { $self[]= yield; },
      ]))
    );
  }

  #[Test]
  public function can_reassing_value() {
    $address= new XmlString('<book>Book #1</book>');
    Assert::equals(
      'Book #1',
      $address->next(new ValueOf(null, [
        '.' => function(&$self) { $self= yield; },
      ]))
    );
  }

  #[Test]
  public function can_select_multiple() {
    $address= new XmlString('<tests><unit>a</unit><unit>b</unit><integration>c</integration><system>d</system></tests>');
    Assert::equals(
      ['unit:a', 'unit:b', 'integration:c'],
      $address->next(new ValueOf([], [
        'unit|integration' => function(&$self, $name) { $self[]= $name.':'.yield; },
      ]))
    );
  }

  #[Test]
  public function combine_values_during_finalization() {
    $address= new XmlString('<created><date>2022-10-31</date><time>16:26:53</time></created>');
    $values= [];
    Assert::equals(
      ['date' => new Date('2022-10-31 16:26:53')],
      $address->next(new ValueOf([], [
        'date' => function(&$self) use(&$values) { $values[0]= yield; },
        'time' => function(&$self) use(&$values) { $values[1]= yield; },
        '/'    => function(&$self) use(&$values) { $self['date']= new Date(implode(' ', $values)); },
      ]))
    );
  }

  #[Test, Values([1, 2, 3])]
  public function call_value_repeatedly($times) {
    $definition= new ValueOf([], [
      '@*' => function(&$self, $name) { $self[$name]= yield; },
      '.'  => function(&$self) { $self['name']= yield; }
    ]);

    $address= new XmlString('<book asin="B01N1UPZ10" author="Test">Name</book>');
    for ($i= 1; $i <= $times; $i++) {
      Assert::equals(
        ['name' => 'Name', 'asin' => 'B01N1UPZ10', 'author' => 'Test'],
        $address->value($definition),
        "Invocation #{$i}"
      );
    }
  }
}