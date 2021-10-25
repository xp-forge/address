<?php namespace util\address;

use lang\Reflection;

/**
 * Creates a record based on a given type and addresses. Records
 * are defined as having an all-arg constructor.
 *
 * @test  util.address.unittest.RecordOfTest
 */
class RecordOf implements Definition {
  private $constructor, $addresses;

  /**
   * Creates a new object definition
   *
   * @param  lang.XPClass|string $type
   * @param  [:function(util.address.Iteration): void] $addresses
   */
  public function __construct($type, $addresses) {
    $this->constructor= Reflection::of($type)->constructor();
    $this->addresses= $addresses;
  }

  /**
   * Address a given path. If nothing is defined, discard value silently.
   *
   * @param  [:var] $named
   * @param  string $path
   * @param  util.address.Iteration $iteration
   * @return void
   */
  private function next(&$named, $path, $iteration) {
    $address= $this->addresses[$path]
      ?? $this->addresses['*']
      ?? ('@' === $path[0] ? $this->addresses['@*'] ?? null : null)
    ;

    $address ? $address($named, $iteration, $path) : $iteration->next();
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return [:var]
   */
  public function create($iteration) {
    $base= $iteration->path().'/';
    $offset= strlen($base);
    $named= [];

    // Select current node
    if ($address= $this->addresses['.'] ?? null) {
      $address($named, $iteration, '.');
    } else {
      $iteration->next();
    }

    // Select attributes and children
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $offset)) {
      $this->next($named, substr($iteration->path(), $offset), $iteration);
    }
    return $this->constructor->newInstance($named);
  }
}
