<?php namespace util\address;

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
  public function __construct($type, $addresses) {
    $this->type= Reflection::of($type);
    if (!$this->type->instantiable()) {
      throw new IllegalArgumentException('Given type '.$this->type->name().' is not instantiable');
    }

    $this->addresses= [];

    // Handle BC: Up until (and including 3.0.0), functions of the form
    // `function($it) { $this->member= $it->next(); }` were passed. Trigger
    // deprecation warning and rewrite accordingly.
    foreach ($addresses as $path => $address) {
      $t= typeof($address);
      if (1 === sizeof($t->signature())) {
        trigger_error('Use function(object, util.address.Iteration) instead!', E_USER_DEPRECATED);
        $this->addresses[$path]= function($instance, $iteration) use($address) {
          $address->bindTo($instance, $instance)->__invoke($iteration);
        };
      } else {
        $this->addresses[$path]= $address->bindTo(null, $this->type->literal());
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
