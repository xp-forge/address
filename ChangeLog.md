XML streams to objects
======================

## ?.?.? / ????-??-??

* Made `util.address.ObjectOf` (and *RecordOf*) raise exceptions if
  the given type is not instantiable
  (@thekid)
* Merged PR #9: Refactor `util.address.ObjectOf` to accept functions
  typed `function(object, util.address.Iteration): void` instead of
  binding *$this* to the target object instance.
  (@thekid)
* Merged PR #8: Add `util.address.MapOf` to create maps. Comparable
  to *RecordOf* in all aspects except the return type.
  (@thekid)
* Merged PR #7: Add `util.address.RecordOf` for record classes. While
  the *ObjectOf* class modifies members directly, this class modifies
  named constructor arguments.
  (@thekid)

## 3.0.0 / 2021-10-24

* Removed dependency on `xp-framework/collections` library, add a `Pair`
  class of our own
  (@thekid)
* Made library compatible with XP 11, dropped XP 9 (and lower versions)
  (@thekid)
* Merged PR #6: Add new `util.address.ObjectOf`. It replaces the old
  *CreationOf* API, which requires objects to have a `with()` method
  returning an *InstanceCreation* instance
  (@thekid)
* Merged PR #5: Refactor code base, dropping dependency on the *partial*
  library
  (@thekid)

## 2.0.0 / 2020-04-10

* Implemented xp-framework/rfc#334: Drop PHP 5.6:
  . **Heads up:** Minimum required PHP version now is PHP 7.0.0
  . Rewrote code base, grouping use statements
  . Converted `newinstance` to anonymous classes
  (@thekid)

## 1.0.0 / 2020-04-04

* Made compatible with XP 10, PHP 7+ - @thekid

## 0.5.0 / 2016-01-23

* Adapted to changes in xp-forge/partial 0.6.0 - @thekid

## 0.4.0 / 2015-07-12

* **Heads up: Changed to depend on xp-forge/partial** (instead of on
  xp-forge/creation, which is deprecated)
  - Bumped minimum PHP version to PHP 5.6 - @thekid
  - Rewrote codebase to make use of ValueObject trait - @thekid
  (@thekid)

## 0.3.0 / 2015-06-14

* Added HHVM support by fixing xp-forge/address#3 - @thekid
* Added forward compatibility with PHP7 - @thekid

## 0.2.0 / 2015-02-12

* Changed dependency to use XP 6.0 (instead of dev-master) - @thekid
* Added iteration support to `util.address.Address` - @thekid
* Merge PR #1: Support for XML attributes - @thekid

## 0.1.0 / 2014-12-21

* Hello World! First release - @thekid