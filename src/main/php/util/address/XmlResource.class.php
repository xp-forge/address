<?php namespace util\address;

use lang\XPClass;
use lang\reflect\Package;

/**
 * XML class loader resource input
 *
 * @test  xp://util.address.unittest.XmlInputTest
 */
class XmlResource extends Address {
  private $package, $name;

  /**
   * Creates a new resource-based XML input
   *
   * @param  var $arg Either a lang.reflect.Package, a lang.XPClass or a string referring to a package
   * @param  string $name
   */
  public function __construct($arg, $name) {
    if ($arg instanceof XPClass) {
      $this->package= $arg->getPackage();
    } else if ($arg instanceof Package) {
      $this->package= $arg;
    } else {
      $this->package= Package::forName(strtr($arg, '\\', '.'));
    }
    $this->name= $name;
  }

  /** @return php.Iterator */
  protected function newIterator() { return new XmlIterator($this->package->getResourceAsStream($this->name)->in()); }
}