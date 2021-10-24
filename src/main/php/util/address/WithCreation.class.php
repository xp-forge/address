<?php namespace util\address;

trait WithCreation {

  /** @return util.address.InstanceCreation */
  public static function with() {
    return InstanceCreation::of(self::class);
  }
}