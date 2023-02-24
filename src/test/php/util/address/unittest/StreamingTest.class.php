<?php namespace util\address\unittest;

use lang\{IllegalStateException, Runnable};
use test\{Assert, Expect, Ignore, Test};
use util\NoSuchElementException;
use util\address\{ArrayOf, CreationOf, Definition, XmlStreaming};

class StreamingTest {

  /** @return util.address.Definition */
  private function asMap() {
    return new class() implements Definition {
      public function create($it) { return [$it->path() => $it->next()]; }
    };
  }

  #[Test]
  public function path() {
    $address= new XmlStreaming('<doc/>');
    Assert::equals('/', $address->path());
  }

  #[Test]
  public function path_after_end() {
    $address= new XmlStreaming('<doc/>');
    $address->next();
    Assert::null($address->path());
  }

  #[Test]
  public function empty_node_value() {
    $address= new XmlStreaming('<doc/>');
    Assert::null($address->next());
  }

  #[Test]
  public function value_for_node_with_content() {
    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::equals('Test', $address->next());
  }

  #[Test, Expect(NoSuchElementException::class)]
  public function next_after_end() {
    $address= new XmlStreaming('<doc/>');
    $address->next();
    $address->next();
  }

  #[Test, Expect(NoSuchElementException::class)]
  public function value_after_end() {
    $address= new XmlStreaming('<doc/>');
    $address->next();
    $address->value();
  }

  #[Test]
  public function next_after_resetting() {
    $address= new XmlStreaming('<doc>Test</doc>');
    $address->reset();
    Assert::equals('Test', $address->next());
  }

  #[Test]
  public function next_after_next_and_resetting() {
    $address= new XmlStreaming('<doc>Test</doc>');
    $address->next();
    $address->reset();
    Assert::equals('Test', $address->next());
  }

  #[Test]
  public function value() {
    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::equals('Test', $address->value());
  }

  #[Test]
  public function valid() {
    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::true($address->valid());
  }

  #[Test]
  public function valid_after_end() {
    $address= new XmlStreaming('<doc>Test</doc>');
    $address->next();
    Assert::false($address->valid());
  }

  #[Test]
  public function valid_after_resetting() {
    $address= new XmlStreaming('<doc>Test</doc>');
    $address->reset();
    Assert::true($address->valid());
  }

  #[Test]
  public function valid_after_value() {
    $address= new XmlStreaming('<doc>Test</doc>');
    $address->value();
    Assert::true($address->valid());
  }

  #[Test]
  public function valid_after_next_and_resetting() {
    $address= new XmlStreaming('<doc>Test</doc>');
    $address->next();
    $address->reset();
    Assert::true($address->valid());
  }

  #[Test]
  public function iteration() {
    $address= new XmlStreaming('<doc><nested>Test</nested></doc>');
    $actual= [];
    while ($address->valid()) {
      $actual[]= [$address->path() => $address->next()];
    }
    Assert::equals([['/' => null], ['//nested' => 'Test']], $actual);
  }

  #[Test]
  public function next_with_definition() {
    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->next($this->asMap()));
  }

  #[Test]
  public function value_with_definition() {
    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($this->asMap()));
  }

  #[Test]
  public function repeated_value_with_definition() {
    $definition= $this->asMap();

    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($definition), '#1');
    Assert::equals(['/' => 'Test'], $address->value($definition), '#2');
  }

  #[Test]
  public function next_after_value_with_definition() {
    $definition= $this->asMap();

    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($definition), '#1');
    Assert::equals(['/' => 'Test'], $address->next($definition), '#2');
  }

  #[Test]
  public function repeated_value_with_varying_definition() {
    $asValue= new class() implements Definition {
      public function create($it) { return $it->next(); }
    };

    $address= new XmlStreaming('<doc>Test</doc>');
    Assert::equals(['/' => 'Test'], $address->value($this->asMap()), '#1');
    Assert::equals('Test', $address->value($asValue), '#2');
  }

  #[Test]
  public function iteration_support() {
    $address= new XmlStreaming('<doc><a>A</a><b>B</b></doc>');
    Assert::equals(['/' => null, '//a' => 'A', '//b' => 'B'], iterator_to_array($address));
  }

  #[Test]
  public function iteration_support_for_empty_document() {
    $address= new XmlStreaming('<doc/>');
    Assert::equals(['/' => null], iterator_to_array($address));
  }

  #[Test]
  public function iteration_valid_with_value() {
    $actual= [];
    $address= new XmlStreaming('<doc><a>A</a><b>B</b></doc>');
    foreach ($address as $path => $value) {
      $actual[$path]= [$address->value(), $address->valid()];
    }
    Assert::equals(['/' => [null, true], '//a' => ['A', true], '//b' => ['B', true]], $actual);
  }

  #[Test]
  public function iteration_valid_with_value_and_definition() {
    $definition= new class() implements Definition {
      public function create($iteration) { return $iteration->next(); }
    };

    $actual= [];
    $address= new XmlStreaming('<doc><a>A</a><b>B</b></doc>');
    foreach ($address as $path => $value) {
      $actual[$path]= [$address->value($definition), $address->valid()];
    }
    Assert::equals(['/' => [null, true], '//a' => ['A', true], '//b' => ['B', true]], $actual);
  }

  #[Test]
  public function pointers() {
    $actual= [];

    $address= new XmlStreaming('<doc><a>A</a><b>B</b></doc>');
    foreach ($address->pointers() as $path => $pointer) {
      $actual[$path]= $pointer->value();
    }
    Assert::equals(['/' => null, '//a' => 'A', '//b' => 'B'], $actual);
  }

  #[Test]
  public function filtered_pointers() {
    $actual= [];

    $address= new XmlStreaming('<tests><unit>A</unit><unit>B</unit><integration>C</integration></tests>');
    foreach ($address->pointers('//unit') as $path => $pointer) {
      $actual[]= $pointer->value();
    }
    Assert::equals(['A', 'B'], $actual);
  }

  #[Test]
  public function pointers_with_definition() {
    $definition= $this->asMap();
    $actual= [];

    $address= new XmlStreaming('<doc><a>A</a><b>B</b></doc>');
    foreach ($address->pointers() as $path => $pointer) {
      $actual+= $pointer->value($definition);
    }
    Assert::equals(['/' => null, '//a' => 'A', '//b' => 'B'], $actual);
  }

  #[Test]
  public function pointers_can_resume() {
    $actual= [];

    $address= new XmlStreaming('<doc><a>A</a><b>B</b></doc>');
    $address->next();
    foreach ($address->pointers() as $path => $pointer) {
      $actual[$path]= $pointer->value();
    }
    Assert::equals(['//a' => 'A', '//b' => 'B'], $actual);
  }
}