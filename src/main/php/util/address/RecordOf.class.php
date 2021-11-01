<?php namespace util\address;

use lang\reflection\InvocationFailed;
use lang\{Reflection, IllegalArgumentException};
use util\Objects;

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
   * @param  [:function(object, util.address.Iteration, string): void] $addresses
   * @throws lang.IllegalArgumentException if type is not instantiable or doesn't have a constructor
   */
  public function __construct($type, array $addresses) {
    $reflect= Reflection::of($type);
    if (!$reflect->instantiable()) {
      throw new IllegalArgumentException('Given type '.$reflect->name().' is not instantiable');
    }
    if (null === ($this->constructor= $reflect->constructor())) {
      throw new IllegalArgumentException('Given type '.$reflect->name().' does not have a constructor');
    }

    $this->addresses= $addresses;
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return [:var]
   * @throws lang.Throwable
   */
  public function create($iteration) {
    try {
      return $this->constructor->newInstance($this->next($iteration, []));
    } catch (InvocationFailed $e) {
      throw $e->getCause();
    }
  }
}
