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

    foreach ($addresses as $path => $address) {
      $r= new ReflectionFunction($address);
      if ($r->isGenerator()) {
        $handler= $address->bindTo($r->getClosureThis(), $this->type->literal());
      } else {
        '/' === $path || trigger_error('Use function(var, string) instead!', E_USER_DEPRECATED);
        $bound= $address->bindTo($r->getClosureThis(), $this->type->literal());
        $handler= function(&$result, $path, $iteration) use($bound) {
          $bound($result, $iteration, $path);
          return [];
        };
      }

      foreach (explode('|', $path) as $match) {
        $this->addresses[$match]= $handler;
      }
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
