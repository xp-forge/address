<?php namespace util\address;

/**
 * Creates an array of elements based on a given component definition
 *
 * @test  xp://util.address.unittest.MapOfTest
 */
class MapOf extends \lang\Object implements Definition {
  private $definitions;

  /**
   * Creates a new array definition
   *
   * @param  [:util.address.Definition] $definitions
   */
  public function __construct($definitions= []) {
    $this->definitions= $definitions;
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return var
   */
  public function create($iteration) {
    $base= $iteration->base();
    $length= strlen($base);

    $r= [];
    while (0 === strncmp($iteration->path(), $base, $length)) {
      $key= substr($iteration->path(), $length);

      if (isset($this->definitions[$key])) {
        $iteration->next();
        $r[$key]= $iteration->next($this->definitions[$key], $base.$key);
      } else {
        $r[$key]= $iteration->next(null);
      }
    }

    return $r;
  }
}