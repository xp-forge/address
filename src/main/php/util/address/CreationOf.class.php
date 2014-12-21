<?php namespace util\address;

use util\objects\InstanceCreation;

class CreationOf extends \lang\Object implements Definition {
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
   * @param  string $path
   * @param  util.address.Iteration $iteration
   * @return void
   */
  protected function next($path, $iteration) {
    if (isset($this->addresses[$path])) {
      $this->addresses[$path]->bindTo($this->creation)->__invoke($iteration);
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
    $base= $iteration->path().'/';
    $length= strlen($base);

    $this->next('.', $iteration);
    while (0 === strncmp($iteration->path(), $base, $length)) {
      $this->next(substr($iteration->path(), $length), $iteration);
    }

    return $this->creation->create();
  }
}