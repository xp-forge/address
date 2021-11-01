<?php namespace util\address;

use lang\Reflection;

/**
 * Creates a record based on a given type and addresses. Records
 * are defined as having an all-arg constructor.
 *
 * @test  util.address.unittest.RecordOfTest
 */
class RecordOf extends ByAddresses {
  private $constructor;

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
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return [:var]
   */
  public function create($iteration) {
    return $this->constructor->newInstance($this->next($iteration, []));
  }
}
