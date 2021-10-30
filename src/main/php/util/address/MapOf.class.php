<?php namespace util\address;

use lang\Reflection;

/**
 * Creates a map based on given addresses.
 *
 * @test  util.address.unittest.MapOfTest
 */
class MapOf implements Definition {
  private $addresses;

  /**
   * Creates a new map definition
   *
   * @param  [:function([:var], util.address.Iteration, string): void] $addresses
   */
  public function __construct($addresses) {
    $this->addresses= $addresses;
  }

  /**
   * Address a given path. If nothing is defined, discard value silently.
   *
   * @param  [:var] $result
   * @param  string $path
   * @param  util.address.Iteration $iteration
   * @return void
   */
  private function next(&$result, $path, $iteration) {
    if ('@' === $path[0]) {
      $address= $this->addresses[$path] ?? $this->addresses['@*'] ?? null;
      $path= substr($path, 1);
    } else {
      $address= $this->addresses[$path] ?? $this->addresses['*'] ?? null;
    }

    $address ? $address($result, $iteration, $path) : $iteration->next();
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
    $map= [];

    // Select current node
    if ($address= $this->addresses['.'] ?? null) {
      $address($map, $iteration, '.');
    } else {
      $iteration->next();
    }

    // Select attributes and children
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $offset)) {
      $this->next($map, substr($iteration->path(), $offset), $iteration);
    }
    return $map;
  }
}
