<?php namespace util\address\unittest;

use lang\{XPClass, Error, Runnable, IllegalArgumentException};
use unittest\actions\RuntimeVersion;
use unittest\{Action, Assert, Expect, Test, Values};
use util\address\{RecordOf, XmlString};

class RecordOfTest {

  /** @return util.address.Address */
  private function address() { return new XmlString('<book author="Test">Name</book>'); }

  /** @return iterable */
  private function types() {
    return [
      [Book::class],
      ['util.address.unittest.Book'],
      [XPClass::forName('util.address.unittest.Book')],
    ];
  }

  #[Test, Values('types')]
  public function can_create($type) {
    new RecordOf($type, []);
  }

  #[Test, Expect(class: IllegalArgumentException::class, withMessage: '/Given type .+ does not have a constructor/')]
  public function cannot_pass_classes_without_constructor() {
    new RecordOf(self::class, []);
  }

  #[Test, Expect(class: IllegalArgumentException::class, withMessage: '/Given type .+ is not instantiable/')]
  public function cannot_pass_interfaces() {
    new RecordOf(Runnable::class, []);
  }

  #[Test]
  public function instantiates_type() {
    $definition= new RecordOf(Book::class, [
      '@author' => function(&$args, $it) { $args['author']= new Author($it->next()); },
      '.'       => function(&$args, $it) { $args['name']= $it->next(); },
    ]);

    Assert::equals(new Book('Name', new Author('Test')), $this->address()->next($definition));
  }

  #[Test]
  public function optional_args_can_be_omitted() {
    $definition= new RecordOf(Book::class, [
      '.' => function(&$args, $it) { $args['name']= $it->next(); },
    ]);

    Assert::equals(new Book('Name'), $this->address()->next($definition));
  }

  #[Test, Expect(class: Error::class, withMessage: '/Unknown named parameter .+/')]
  public function excess_args_raise_an_error() {
    $this->address()->next(new RecordOf(Book::class, [
      '.' => function(&$args, $it) { $args['name']= $it->next(); },
      '/' => function(&$args, $it) { $args['extra']= true; },
    ]));
  }

  #[Test, Expect(class: Error::class, withMessage: '/Argument .+ must be (of type|an instance of)/')]
  public function raises_error_if_required_argument_is_mistyped() {
    $this->address()->next(new RecordOf(Book::class, [
      '@author' => function(&$args, $it) { $args['author']= $it->next(); }, // Missing `new Author(...)`
      '.'       => function(&$args, $it) { $args['name']= $it->next(); },
    ]));
  }

  /** @see https://wiki.php.net/rfc/too_few_args, implemented in PHP 7.1 */
  #[Test, Action(eval: 'new RuntimeVersion(">=7.1")'), Expect(class: Error::class, withMessage: '/Too few arguments .+/')]
  public function raises_error_if_required_argument_is_missing() {
    $this->address()->next(new RecordOf(Book::class, []));
  }

  #[Test, Action(eval: 'new RuntimeVersion("<7.1")'), Expect(IllegalArgumentException::class)]
  public function php70_raises_iae_if_required_argument_is_missing() {
    $this->address()->next(new RecordOf(Book::class, []));
  }
}