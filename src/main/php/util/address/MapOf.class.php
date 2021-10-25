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
   * @param  [:function(util.address.Iteration): void] $addresses
   */
  public function __construct($addresses) {
    $this->addresses= $addresses;
  }

  /**
   * Address a given path. If nothing is defined, discard value silently and
   * return an empty map.
   *
   * @param  string $path
   * @param  util.address.Iteration $iteration
   * @return [:var]
   */
  protected function next($path, $iteration) {
    if ($address= $this->addresses[$path] ?? null) {
      return $address($iteration, $path);
    } else if ('.' !== $path[0] && $address= $this->addresses['*'] ?? null) {
      return $address($iteration, $path);
    } else if ('@' === $path[0] && $address= $this->addresses['@*'] ?? null) {
      return $address($iteration, substr($path, 1));
    }

    $iteration->next();
    return [];
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return object
   */
  public function create($iteration) {
    $base= $iteration->path().'/';
    $offset= strlen($base);

    $map= $this->next('.', $iteration);
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $offset)) {
      $map+= $this->next(substr($iteration->path(), $offset), $iteration);
    }

    return $map;
  }
}
