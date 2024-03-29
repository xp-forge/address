XML streams to objects
======================

## ?.?.? / ????-??-??

## 6.1.1 / 2024-03-29

* Fixed "Implicitly marking parameter ... as nullable is deprecated"
  warnings
  (@thekid)

## 6.1.0 / 2024-03-24

* Made compatible with XP 12 - @thekid

## 6.0.0 / 2024-02-04

* Merged PR #23: Remove deprecated XmlStream, XmlFile, XmlString and
  XmlResource classes
  (@thekid)

## 5.4.0 / 2023-11-04

* Merged PR #24: Handle `xml:space` attribute and preserve whitespace
  (@thekid)
* Added PHP 8.4 to the test matrix - @thekid

## 5.3.0 / 2023-03-03

* **Heads up:** Deprecated XmlStream, XmlFile, XmlString and XmlResource
  in favor of the new entry point class `util.address.XmlStreaming`.
  (@thekid)
* Merged PR #21: Join various input sources into one new API: XmlStreaming
  (@thekid)
* Merged PR #20: Migrate to new testing library - @thekid

## 5.2.1 / 2023-01-21

* Fixed compatibility with XP reflection 2.8.0 - @thekid

## 5.2.0 / 2022-07-18

* Made `XmlStream` and `XmlFile` implement the `lang.Closeable` interface
  (@thekid)
* Fixed *Undefined property: util\address\XmlStream::$file* warnings
  (@thekid)
* Changed `XmlStream::toString()` to check if InputStream implementations
  provide a `toString()` method even if they do not implement `lang.Value`.
  See https://github.com/xp-framework/core/issues/310
  (@thekid)

## 5.1.1 / 2022-01-24

* Fixed PHP 8.2 compatiblity - @thekid
* Made compatible with XP reflection 2.0.0 - @thekid

## 5.1.0 / 2021-11-21

* Merged PR #19: Support for inline doctype and entities. External
  DTDs and external entities are not supported.
  (@thekid)

## 5.0.0 / 2021-11-14

This major release deprecates the `$it->next()` syntax in favor of
using `yield`. See #16, implemented in 4.2.0.

* Merged PR #18: Version 5 cleanup, implementing #17. This means the
  deprecated ArrayOf and Enclosing classes are gone as well as *$this*
  being bound to the created instance in `util.address.ObjectOf`.
  (@thekid)

## 4.2.0 / 2021-11-14

* Merged PR #16: Use `yield` inside address functions. Using functions
  of the form `fn(&$self) => $self['item']= yield;` inside definitions
  is the new preferred way to process the values!
  (@thekid)

## 4.1.0 / 2021-11-13

* Merged PR #15: Add `Address::pointers()` to iterate over paths and
  pointers, optionally filtering by a given path
  (@thekid)
* Merged PR #14: Make util.address.Address subclasses implement the
  lang.Value interface, making them comparable and giving them more
  readable string representations
  (@thekid)
* Merged PR #13: Introduce `Address::value()` to return current value
  (@thekid)

## 4.0.1 / 2021-11-01

* Fixed *Cannot unbind $this of closure using $this* - @thekid

## 4.0.0 / 2021-11-01

This major release offers three ways to create values from XML: Using
ValueOf, ObjectOf or RecordOf. The first is the most generic form and
accepts a default value while the latter two produce objects of a given
type, differing in the way they create these.

* Merged PR #11: Deprecate the ArrayOf and Enclosing classes, folding
  their functionality into `util.address.ValueOf`
  (@thekid)
* Renamed MapOf to `util.address.ValueOf` and require a default value
  to be passed to its constructor
  (@thekid)
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

This release brings this library up-to-date by dropping dependencies
on archived libraries and adding a more generic object instantation
mechanism not reliant on a certain class architecture.

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

This release is the first release to no longer run on PHP 5, implementing
xp-framework/rfc#334.

* **Heads up:** Minimum required PHP version now is PHP 7.0.0 - @thekid
* Rewrote code base, grouping use statements - @thekid
* Converted `newinstance` to anonymous classes - @thekid

## 1.0.0 / 2020-04-04

First major release, compatible with the current XP and PHP versions.

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