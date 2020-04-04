<?php namespace util\address;

use util\NoSuchElementException;

/**
 * Base class for all XML inputs
 *
 * @test  xp://util.address.unittest.AddressTest
 */
abstract class Address implements \IteratorAggregate {
  private $iterator;

  /**
   * Gets iterator
   *
   * @param  bool $rewind Whether to initially rewind the iterator
   * @return php.Iterator
   */
  public function getIterator($rewind= false) {
    if (null === $this->iterator) {
      $this->iterator= $this->newIterator();
      if ($rewind) {
        $this->iterator->rewind();
      }
    }
    return $this->iterator;
  }

  /** @return php.Iterator */
  protected abstract function newIterator();

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
   * Returns the next value according to the given definition
   *
   * @param  util.address.Definition $definition
   * @param  string $base
   * @return var
   * @throws util.NoSuchElementException if there are no more eements
   */
  public function next(Definition $definition= null, $base= '/') {
    if ($definition) {
      return $definition->create(new Iteration($this, $base));
    } else {
      $it= $this->getIterator(true);
      if ($it->valid()) {
        $value= $it->current();
        $it->next();
        return $value;
      } else {
        throw new NoSuchElementException('No more elements in iterator');
      }
    }
  }
}