<?php namespace util\address;

use IteratorAggregate, Traversable;
use util\NoSuchElementException;

/**
 * Base class for all XML inputs
 *
 * @test  xp://util.address.unittest.AddressTest
 */
abstract class Address implements IteratorAggregate {
  private $iterator;

  /**
   * Gets iterator
   *
   * @param  bool $rewind Whether to initially rewind the iterator
   * @return php.Iterator
   */
  public function getIterator($rewind= false): Traversable {
    if (null === $this->iterator) {
      $this->iterator= new XmlIterator($this->stream());
      if ($rewind) {
        $this->iterator->rewind();
      }
    }
    return $this->iterator;
  }

  /** @return io.streams.InputStream */
  protected abstract function stream();

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
   * @param  string $base
   * @return var
   * @throws util.NoSuchElementException if there are no more elements
   */
  public function value(Definition $definition= null, $base= '/') {
    $it= $this->getIterator(true);
    if ($it->valid()) {
      return null === $definition ? $it->current() : $it->value($definition, $this, $base, true);
    }

    throw new NoSuchElementException('No more elements in iterator');    
  }

  /**
   * Returns the next value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @param  string $base
   * @return var
   * @throws util.NoSuchElementException if there are no more elements
   */
  public function next(Definition $definition= null, $base= '/') {
    $it= $this->getIterator(true);
    if ($it->valid()) {
      $value= null === $definition ? $it->current() : $it->value($definition, $this, $base, false);
      $it->next();
      return $value;
    }

    throw new NoSuchElementException('No more elements in iterator');
  }
}