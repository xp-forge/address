<?php namespace util\address;

use lang\IllegalStateException;

/** @deprecated Use ValueOf instead! */
class Enclosing implements Definition {
  private $path;

  /**
   * Creates a new enclosing definition
   *
   * @param  string $path
   */
  public function __construct($path) {
    $this->path= $path;
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return var
   */
  public function create($iteration) {
    if (null !== $this->path && $this->path !== $iteration->path()) {
      throw new IllegalStateException('Enclosing element mismatch, expecting "'.$this->path.'", have "'.$iteration->path().'"');
    }

    $iteration->next();
    return $iteration;
  }
}