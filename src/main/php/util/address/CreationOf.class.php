<?php namespace util\address;

/**
 * Creates an object based on a given instance creation
 *
 * @test  xp://util.address.unittest.CreationOfTest
 */
class CreationOf implements Definition {
  private $creation, $addresses;

  /**
   * Creates a new creation definition
   *
   * @param  var $arg Either an InstanceCreation, an XPClass or a string referring to a class
   * @param  [:function(util.address.Iteration): void] $addresses
   */
  public function __construct($arg, $addresses) {
    if ($arg instanceof InstanceCreation) {
      $this->creation= $arg;
    } else {
      $this->creation= InstanceCreation::of($arg);
    }
    $this->addresses= $addresses;
  }

  /**
   * Address a given path. If nothing is defined, discard value silently.
   *
   * @param  util.objects.InstanceCreation $creation
   * @param  string $path
   * @param  util.address.Iteration $iteration
   * @return void
   */
  protected function next($creation, $path, $iteration) {
    if (isset($this->addresses[$path])) {
      $this->addresses[$path]->bindTo($creation)->__invoke($iteration);
    } else {
      $iteration->next();
    }
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return var
   */
  public function create($iteration) {
    $return= clone $this->creation;

    $base= $iteration->path().'/';
    $length= strlen($base);

    $this->next($return, '.', $iteration);
    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $length)) {
      $this->next($return, substr($iteration->path(), $length), $iteration);
    }

    return $return->create();
  }
}