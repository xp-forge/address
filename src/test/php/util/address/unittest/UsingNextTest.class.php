<?php namespace util\address\unittest;

use unittest\{Assert, Test};
use util\address\{ValueOf, XmlString};

class UsingNextTest {
  const BOOK = '<book id="1"><name>Name</name><author/><date>1977-12-14</date></book>';

  #[Test]
  public function next_without_definition() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new ValueOf([], [
        'name' => function(&$self, $it) { $self['name']= $it->next(); }
      ]))
    );
  }

  #[Test]
  public function next_with_definition() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['name' => 'Name', 'author' => null, 'date' => '1977-12-14'],
      $address->next(new ValueOf([], [
        '.' => function(&$self, $it) {
          $self= $it->next(new ValueOf(null, ['*' => function(&$self, $it, $path) {
            $self[$path]= $it->next();
          }]));
        },
      ]))
    );
  }
}