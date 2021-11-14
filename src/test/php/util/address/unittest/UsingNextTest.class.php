<?php namespace util\address\unittest;

use unittest\actions\RuntimeVersion;
use unittest\{Assert, Action, Test};
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

  /** @see https://wiki.php.net/rfc/arrow_functions_v2 */
  #[Test, Action(eval: 'new RuntimeVersion(">=7.4")')]
  public function can_use_fn() {
    $address= new XmlString(self::BOOK);
    Assert::equals(
      ['name' => 'Name'],
      $address->next(new ValueOf([], [
        'name' => eval('return fn(&$self, $it) => $self["name"]= $it->next();')
      ]))
    );
  }
}