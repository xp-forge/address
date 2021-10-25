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
   * Address a given path. If nothing is defined, discard value silently.
   *
   * @param  [:var] $map
   * @param  string $path
   * @param  util.address.Iteration $iteration
   * @return void
   */
  protected function next(&$map, $path, $iteration) {
    if ($address= $this->addresses[$path] ?? ('.' === $path ? null : $this->addresses['*'] ?? null)) {
      foreach ($address($iteration, $path) as $name => $value) {
        $map[$name]= $value;
      }
    } else {
      $iteration->next();
    }
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return object
   */
  public function create($iteration) {
    $map= [];
    $base= $iteration->path().'/';
    $length= strlen($base);

    $this->next($map, '.', $iteration);
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $length)) {
      $this->next($map, substr($iteration->path(), $length), $iteration);
    }

    return $map;
  }
}
