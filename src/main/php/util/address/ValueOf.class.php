<?php namespace util\address;

/**
 * Creates a map based on given addresses.
 *
 * @test  util.address.unittest.ValueOfTest
 */
class ValueOf extends ByAddresses {
  private $default;

  /**
   * Creates a new object definition
   *
   * @param  var $default
   * @param  [:function(var, util.address.Iteration, string): void] $addresses
   */
  public function __construct($default, array $addresses) {
    $this->default= $default;
    parent::__construct($addresses);
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return [:var]
   */
  public function create($iteration) {
    return $this->next($iteration, $this->default);
  }
}
