<?php namespace util\address;

/**
 * Creates a map based on given addresses.
 *
 * @test  util.address.unittest.MapOfTest
 */
class MapOf extends ByAddresses {

  /**
   * Creates a new map definition
   *
   * @param  [:function([:var], util.address.Iteration, string): void] $addresses
   */
  public function __construct($addresses= []) {
    $this->addresses= $addresses;
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return [:var]
   */
  public function create($iteration) {
    return $this->next($iteration, []);
  }
}
