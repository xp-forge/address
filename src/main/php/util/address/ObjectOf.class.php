<?php namespace util\address;

use lang\Reflection;

/**
 * Creates an object based on a given type and addresses
 *
 * @test  util.address.unittest.ObjectOfTest
 */
class ObjectOf implements Definition {
  private $type, $addresses;

  /**
   * Creates a new object definition
   *
   * @param  lang.XPClass|string $type
   * @param  [:function(object, util.address.Iteration): void] $addresses
   */
  public function __construct($type, $addresses) {
    $this->type= Reflection::of($type);
    $this->addresses= [];

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
   * Address a given path. If nothing is defined, discard value silently.
   *
   * @param  object $instance
   * @param  string $path
   * @param  util.address.Iteration $iteration
   * @return void
   */
  protected function next($instance, $path, $iteration) {
    if ($address= $this->addresses[$path] ?? null) {
      $address($instance, $iteration);
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
    $return= $this->type->initializer(null)->newInstance();

    $base= $iteration->path().'/';
    $length= strlen($base);

    $this->next($return, '.', $iteration);
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $length)) {
      $this->next($return, substr($iteration->path(), $length), $iteration);
    }

    return $return;
  }
}
