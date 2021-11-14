<?php namespace util\address\unittest;

use unittest\{Assert, Test};
use util\Date;
use util\address\{ValueOf, XmlString};

class YieldTest {

  #[Test]
  public function yield_without_argument() {
    $address= new XmlString('<book><name>Name</name></book>');
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new ValueOf([], [
        'name' => function(&$self, $it) { $self['name']= yield; }
      ]))
    );
  }

  #[Test]
  public function yield_with_definition() {
    $address= new XmlString('<book><name>Name</name><author>Test</author></books>');
    Assert::equals(
      ['name' => 'Name', 'author' => 'Test'],
      $address->next(new ValueOf([], [
        '.' => function(&$self, $it) {
          $self= yield new ValueOf(null, ['*' => function(&$self, $it, $path) {
            $self[$path]= yield;
          }]);
        }
      ]))
    );
  }

  #[Test]
  public function yield_inside_arguments() {
    $address= new XmlString('<book><name>Name</name><date>1977-12-14</date></book>');
    Assert::equals(
      ['name' => 'Name', 'date' => new Date('1977-12-14')],
      $address->next(new ValueOf([], [
        'name' => function(&$self, $it) { $self['name']= yield; },
        'date' => function(&$self, $it) { $self['date']= new Date(yield); },
      ]))
    );
  }
}