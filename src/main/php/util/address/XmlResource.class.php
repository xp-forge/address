<?php namespace util\address;

use lang\XPClass;
use lang\reflect\Package;

/**
 * XML class loader resource input
 *
 * @deprecated by XmlStreaming
 * @test  util.address.unittest.XmlInputTest
 */
class XmlResource extends Address {

  /**
   * Creates a new resource-based XML input
   *
   * @param  lang.XPClass|lang.reflect.Package|string $arg Class or package
   * @param  string $name Resource name
   */
  public function __construct($arg, $name) {
    if ($arg instanceof XPClass) {
      $package= $arg->getPackage();
    } else if ($arg instanceof Package) {
      $package= $arg;
    } else {
      $package= Package::forName(strtr($arg, '\\', '.'));
    }
    parent::__construct($package->getResourceAsStream($name)->in());
  }
}