<?php namespace util\address\unittest;

use lang\{XPClass, Error, Runnable, ClassLoader, IllegalStateException, IllegalArgumentException};
use unittest\actions\RuntimeVersion;
use unittest\{Action, Assert, Expect, Test, Values};
use util\address\{ObjectOf, XmlString};

class ObjectOfTest {

  /** @return util.address.Address */
  private function address() { return new XmlString('<book author="Test">Name</book>'); }

  /** Fixture for can_use_public_methods_from_this() test */
  public function toUpper(string $in): string { return strtoupper($in); }

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
    new ObjectOf($type, []);
  }

  #[Test, Expect(class: IllegalArgumentException::class, withMessage: '/Given type .+ is not instantiable/')]
  public function cannot_pass_interfaces() {
    new ObjectOf(Runnable::class, []);
  }

  #[Test]
  public function instantiates_type() {
    $definition= new ObjectOf(Book::class, [
      '@author' => function($self) { $self->author= new Author(yield); },
      '.'       => function($self) { $self->name= yield; },
    ]);

    Assert::equals(new Book('Name', new Author('Test')), $this->address()->next($definition));
  }

  #[Test]
  public function can_omit_properties() {
    $definition= new ObjectOf(Book::class, [
      '.' => function($self) { $self->name= yield; },
    ]);

    Assert::equals(new Book('Name'), $this->address()->next($definition));
  }

  #[Test]
  public function can_use_public_methods_from_this() {
    $definition= new ObjectOf(Book::class, [
      '.' => function($self) { $self->name= $this->toUpper(yield); },
    ]);

    Assert::equals(new Book('NAME'), $this->address()->next($definition));
  }

  #[Test]
  public function does_not_invoke_constructor() {
    $class= ClassLoader::defineClass('BookWithThrowingConstructor', Book::class, [], [
      '__construct' => function() {
        throw new IllegalStateException('Constructor');
      }
    ]);
    $definition= new ObjectOf($class, [
      '.' => function($self) { $self->name= yield; },
    ]);

    Assert::instance($class, $this->address()->next($definition));
  }

  /** @deprecated */
  #[Test]
  public function deprecated_form_of_address_functions() {
    $definition= new ObjectOf(Book::class, [
      '.' => function($it) { $this->name= $it->next(); },
    ]);
    \xp::gc();

    Assert::equals(new Book('Name'), $this->address()->next($definition));
  }
}