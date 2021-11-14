<?php namespace util\address;

use ReflectionFunction;

/** Base class for ValueOf, ObjectOf and RecordOf */
abstract class ByAddresses implements Definition {
  protected $addresses= [];

  /** @param [:function(var, ?string): Generator] */
  public function __construct($addresses) {
    foreach ($addresses as $path => $address) {
      $r= new ReflectionFunction($address);
      if ($r->isGenerator()) {
        $handler= $address;
      } else {
        $handler= function(&$result, $path, $iteration) use($address) {
          $address($result, $iteration, $path);
          return [];
        };
      }

      foreach (explode('|', $path) as $match) {
        $this->addresses[$match]= $handler;
      }
    }
  }

  /**
   * Invoke the address function. If it uses `yield`, send values.
   *
   * @param  function(var, ?string): Generator
   * @param  var $result
   * @param  util.address.Iteration $iteration
   * @param  string $path
   * @return void
   */
  protected function invoke($address, &$result, $iteration, $path) {
    $r= $address($result, $path, $iteration);
    foreach ($r as $definition) {
      $r->send($iteration->next($definition));
    }
  }

  /**
   * Creates next value
   *
   * @param  util.address.Iteration $iteration
   * @param  var $result
   * @return var The given result
   */
  protected function next($iteration, $result) {
    $base= $iteration->path().'/';
    $offset= strlen($base);

    // Select current node
    if ($address= $this->addresses['.'] ?? null) {
      $this->invoke($address, $result, $iteration, '.');
    } else {
      $iteration->next();
    }

    // Select attributes and children
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $offset)) {
      $relative= substr($iteration->path(), $offset);
      if ('@' === $relative[0]) {
        $address= $this->addresses[$relative] ?? $this->addresses['@*'] ?? null;
        $relative= substr($relative, 1);
      } else {
        $address= $this->addresses[$relative] ?? $this->addresses['*'] ?? null;
      }

      // Address a given path. If nothing is defined, discard value silently.
      $address ? $this->invoke($address, $result, $iteration, $relative) : $iteration->next();
    }

    // End of current node
    if ($address= $this->addresses['/'] ?? null) {
      $this->invoke($address, $result, $iteration, '/');
    }

    return $result;
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return [:var]
   */
  public abstract function create($iteration);
}
