<?php namespace util\address\unittest;

use unittest\{Assert, Test};
use util\Date;
use util\address\{ValueOf, XmlString};

class YieldTest {
  const BOOK = '<book id="1"><name>Name</name><author/><date>1977-12-14</date></book>';

  #[Test]
  public function yield_without_argument() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new ValueOf([], [
        'name'   => function(&$self) { $self['name']= yield; },
      ]))
    );
  }

  #[Test]
  public function yield_with_cast() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['id' => 1],
      $address->next(new ValueOf([], [
        '@id'    => function(&$self) { $self['id']= (int)yield; },
      ]))
    );
  }

  #[Test]
  public function yield_with_null_coalesce() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['author' => '(unknown)'],
      $address->next(new ValueOf([], [
        'author' => function(&$self) { $self['author']= yield ?? '(unknown)'; },
      ]))
    );
  }

  #[Test]
  public function yield_inside_arguments() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['date' => new Date('1977-12-14')],
      $address->next(new ValueOf([], [
        'date' => function(&$self) { $self['date']= new Date(yield); },
      ]))
    );
  }

  #[Test]
  public function yield_with_definition() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['name' => 'Name', 'author' => null, 'date' => '1977-12-14'],
      $address->next(new ValueOf([], [
        '.' => function(&$self) {
          $self= yield new ValueOf(null, ['*' => function(&$self, $path) {
            $self[$path]= yield;
          }]);
        },
      ]))
    );
  }
}