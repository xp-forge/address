<?php namespace util\address;

use lang\Reflection;

/**
 * Creates a record based on a given type and addresses. Records
 * are defined as having an all-arg constructor.
 *
 * @test  util.address.unittest.RecordOfTest
 */
class RecordOf implements Definition {
  private $type, $addresses;

  /**
   * Creates a new object definition
   *
   * @param  lang.XPClass|string $type
   * @param  [:function(util.address.Iteration): void] $addresses
   */
  public function __construct($type, $addresses) {
    $this->type= Reflection::of($type);
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
  protected function next(&$named, $path, $iteration) {
    if (isset($this->addresses[$path])) {
      foreach ($this->addresses[$path]->__invoke($iteration) as $name => $value) {
        $named[$name]= $value;
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
    $named= [];
    $base= $iteration->path().'/';
    $length= strlen($base);

    $this->next($named, '.', $iteration);
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $length)) {
      $this->next($named, substr($iteration->path(), $length), $iteration);
    }

    return $this->type->constructor()->newInstance($named);
  }
}
