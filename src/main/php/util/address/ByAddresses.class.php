<?php namespace util\address;

abstract class ByAddresses implements Definition {
  protected $addresses;

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
      $address($result, $iteration, '.');
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
      $address ? $address($result, $iteration, $relative) : $iteration->next();
    }

    // End of current node
    if ($address= $this->addresses['/'] ?? null) {
      $address($result, $iteration, '/');
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
