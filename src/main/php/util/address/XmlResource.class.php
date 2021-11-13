<?php namespace util\address;

use lang\reflect\Package;
use lang\{XPClass, Value};

/**
 * XML class loader resource input
 *
 * @test  util.address.unittest.XmlInputTest
 */
class XmlResource extends Address implements Value {
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

  /** @return io.streams.InputStream */
  protected function stream() { return $this->package->getResourceAsStream($this->name)->in(); }

  /** @return string */
  public function toString() {
    return nameof($this).'<'.$this->name.'@'.$this->package->toString().'>';
  }

  /** @return string */
  public function hashCode() {
    return 'R'.md5($this->name.'@'.$this->package->getName());
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    if ($value instanceof self) {
      $r= $this->name <=> $value->name;
      return 0 === $r ? $this->package->compareTo($value->package) : $r;
    }
    return 1;
  }
}