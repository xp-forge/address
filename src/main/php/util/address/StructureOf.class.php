<?php namespace util\address;

/**
 * Creates a generic data structure from the iteration
 *
 * @test  util.address.unittest.StructureOfTest
 */
class StructureOf implements Definition {

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return var
   */
  public function create($iteration) {
    $base= $iteration->path().'/';
    $offset= strlen($base);
    $value= $iteration->next();

    while (null !== ($path= $iteration->path()) && 0 === strncmp($path, $base, $offset)) {
      if (0 === substr_compare($path, '/[]', -3, 3)) {
        $segments= substr($path, $offset, -3);
        $array= true;
      } else {
        $segments= substr($path, $offset);
        $array= false;
      }

      $ptr= &$value;
      if (strlen($segments) > 0) {
        foreach (explode('/', $segments) as $segment) {
          $ptr= &$ptr[strtr($segment, "\x1D", '/')];
        }
      }

      if ($array) {
        $ptr[]= $this->create($iteration);
      } else {
        $ptr= $iteration->next();
      }
    }
    return $value;
  }
}