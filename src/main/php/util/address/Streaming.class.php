<?php namespace util\address;

use Iterator, IteratorAggregate, Traversable;
use io\Channel;
use io\streams\{InputStream, MemoryInputStream};
use lang\{Closeable, Value};
use util\{NoSuchElementException, Objects};

/**
 * Base class for all streaming inputs
 *
 * @test  util.address.unittest.StreamingTest
 */
abstract class Streaming implements Closeable, Value, IteratorAggregate {
  private $iterator;
  protected $stream;

  /** @param string|io.Channel|io.streams.InputStream $source */
  public function __construct($source) {
    if ($source instanceof InputStream) {
      $this->stream= $source;
    } else if ($source instanceof Channel) {
      $this->stream= $source->in();
    } else {
      $this->stream= new MemoryInputStream($source);
    }
  }

  /** Returns the iterator implementation */ 
  public abstract function iterator(): Iterator;

  /**
   * Gets iterator
   *
   * @param  bool $rewind Whether to initially rewind the iterator
   * @return Traversable
   */
  public function getIterator($rewind= false): Traversable {
    if (null === $this->iterator) {
      $this->iterator= $this->iterator();
      if ($rewind) {
        $this->iterator->rewind();
      }
    }
    return $this->iterator;
  }

  /**
   * Iterate over pointers
   *
   * @param  ?string $filter
   * @return iterable
   */
  public function pointers($filter= null) {
    $it= $this->getIterator(true);
    $pointer= new Pointer($this);

    if (null === $filter) {
      while ($it->valid()) {
        yield $it->key() => $pointer;
        $it->next();
      }
    } else {
      while ($it->valid()) {
        $filter === $it->key() && yield $filter => $pointer;
        $it->next();
      }
    }
  }

  /**
   * Reset this input
   *
   * @throws lang.IllegalStateException if underlying source is not seekable
   */
  public function reset() {
    $this->getIterator()->rewind();
  }

  /**
   * Checks whether there are more elements in this iteration
   *
   * @return bool
   */
  public function valid() {
    return $this->getIterator(true)->valid();
  }

  /** @return string */
  public function path() {
    $it= $this->getIterator(true);
    return $it->valid() ? $it->key() : null;
  }

  /**
   * Returns the current value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @return var
   * @throws util.NoSuchElementException if there are no more elements
   */
  public function value(Definition $definition= null) {
    $it= $this->getIterator(true);
    if ($it->valid()) {
      return null === $definition ? $it->current() : $it->value($definition, true);
    }

    throw new NoSuchElementException('No more elements in iterator');    
  }

  /**
   * Returns the next value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @return var
   * @throws util.NoSuchElementException if there are no more elements
   */
  public function next(Definition $definition= null) {
    $it= $this->getIterator(true);
    if ($it->valid()) {
      $value= null === $definition ? $it->current() : $it->value($definition, false);
      $it->next();
      return $value;
    }

    throw new NoSuchElementException('No more elements in iterator');
  }

  /** @return string */
  public function toString() {

    // Most InputStream instances have a toString() method but do not implement
    // the Value interface, see https://github.com/xp-framework/core/issues/310
    if ($this->stream instanceof Value || method_exists($this->stream, 'toString')) {
      return nameof($this).'<'.$this->stream->toString().'>';
    } else {
      return nameof($this).'<'.Objects::stringOf($this->stream).'>';
    }
  }

  /** @return string */
  public function hashCode() {
    return 'S'.Objects::hashOf($this->stream);
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->stream, $value->stream) : 1;
  }

  /** @return void */
  public function close() { $this->stream->close(); }
}