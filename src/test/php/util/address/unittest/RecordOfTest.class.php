<?php namespace util\address\unittest;

use lang\{Error, IllegalArgumentException, Runnable, XPClass};
use test\verify\Runtime;
use test\{Action, Assert, Expect, Test, Values};
use util\address\{RecordOf, XmlStreaming};

class RecordOfTest {

  /** @return util.address.Address */
  private function address() { return new XmlStreaming('<book author="Test">Name</book>'); }

  /** @return iterable */
  private function types() {
    return [
      [Book::class],
      ['util.address.unittest.Book'],
      [XPClass::forName('util.address.unittest.Book')],
    ];
  }

  #[Test, Values(from: 'types')]
  public function can_create($type) {
    new RecordOf($type, []);
  }

  #[Test, Expect(class: IllegalArgumentException::class, message: '/Given type .+ does not have a constructor/')]
  public function cannot_pass_classes_without_constructor() {
    new RecordOf(self::class, []);
  }

  #[Test, Expect(class: IllegalArgumentException::class, message: '/Given type .+ is not instantiable/')]
  public function cannot_pass_interfaces() {
    new RecordOf(Runnable::class, []);
  }

  #[Test]
  public function instantiates_type() {
    $definition= new RecordOf(Book::class, [
      '@author' => function(&$args) { $args['author']= new Author(yield); },
      '.'       => function(&$args) { $args['name']= yield; },
    ]);

    Assert::equals(new Book('Name', new Author('Test')), $this->address()->next($definition));
  }

  #[Test]
  public function optional_args_can_be_omitted() {
    $definition= new RecordOf(Book::class, [
      '.' => function(&$args) { $args['name']= yield; },
    ]);

    Assert::equals(new Book('Name'), $this->address()->next($definition));
  }

  #[Test, Expect(class: Error::class, message: '/Unknown named parameter .+/')]
  public function excess_args_raise_an_error() {
    $this->address()->next(new RecordOf(Book::class, [
      '.' => function(&$args) { $args['name']= yield; },
      '/' => function(&$args) { $args['extra']= true; },
    ]));
  }

  #[Test, Expect(class: Error::class, message: '/Argument .+ must be (of type|an instance of)/')]
  public function raises_error_if_required_argument_is_mistyped() {
    $this->address()->next(new RecordOf(Book::class, [
      '@author' => function(&$args) { $args['author']= yield; }, // Missing `new Author(...)`
      '.'       => function(&$args) { $args['name']= yield; },
    ]));
  }

  /** @see https://wiki.php.net/rfc/too_few_args, implemented in PHP 7.1 */
  #[Test, Runtime(php: '>=7.1'), Expect(class: Error::class, message: '/Too few arguments .+/')]
  public function raises_error_if_required_argument_is_missing() {
    $this->address()->next(new RecordOf(Book::class, []));
  }

  #[Test, Runtime(php: '<7.1'), Expect(IllegalArgumentException::class)]
  public function php70_raises_iae_if_required_argument_is_missing() {
    $this->address()->next(new RecordOf(Book::class, []));
  }
}