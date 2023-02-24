<?php namespace util\address\unittest;

use io\streams\MemoryInputStream;
use test\{Assert, Test};
use util\address\XmlStreaming;

class XmlStreamingTest {

  /**
   * Creates a new fixture
   *
   * @param  string $bytes
   * @return util.address.XmlStreaming
   */
  private function fixture($bytes= '') {
    return new XmlStreaming(new MemoryInputStream($bytes));
  }

  #[Test]
  public function can_create() {
    $this->fixture();
  }

  #[Test]
  public function hash_of() {
    Assert::notEquals('', $this->fixture()->hashCode());
  }

  #[Test]
  public function string_representation() {
    Assert::equals(
      'util.address.XmlStreaming<io.streams.MemoryInputStream(@0 of 4 bytes)>',
      $this->fixture('Test')->toString()
    );
  }

  #[Test]
  public function equals_itself() {
    $fixture= $this->fixture();
    Assert::equals($fixture, $fixture);
  }

  #[Test]
  public function iteration() {
    $fixture= $this->fixture('<root><a>Test</a></root>');
    Assert::equals(['/' => null, '//a' => 'Test'], iterator_to_array($fixture));
  }

  #[Test]
  public function close() {
    $stream= new class('') extends MemoryInputStream {
      public $closed= false;
      public function close() { $this->closed= true; }
    };

    $fixture= new XmlStreaming($stream);
    $fixture->close();

    Assert::true($stream->closed);
  }
}