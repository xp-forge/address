<?php namespace util\address;

use ReflectionFunction;
use lang\{Reflection, IllegalArgumentException};

/**
 * Creates an object based on a given type and addresses
 *
 * @test  util.address.unittest.ObjectOfTest
 */
class ObjectOf extends ByAddresses {
  private $type;

  /**
   * Creates a new object definition
   *
   * @param  lang.XPClass|string $type
   * @param  [:function(object, util.address.Iteration, string): void] $addresses
   * @throws lang.IllegalArgumentException if type is not instantiable
   */
  public function __construct($type, array $addresses) {
    $this->type= Reflection::of($type);
    if (!$this->type->instantiable()) {
      throw new IllegalArgumentException('Given type '.$this->type->name().' is not instantiable');
    }

    $class= $this->type->literal();
    foreach ($addresses as $path => $address) {
      $reflect= new ReflectionFunction($address);
      $this->add($path, $reflect, $address->bindTo($reflect->getClosureThis(), $class));
    }
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return [:var]
   */
  public function create($iteration) {
    return $this->next($iteration, $this->type->initializer(null)->newInstance());
  }
}
