Creation
========

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/address.svg)](http://travis-ci.org/xp-forge/address)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)

Creates objects from XML input streams while parsing them.

Example
-------

```php
<?php namespace com\example\model;

class Book extends \lang\Object { use \util\objects\CreateWith;
  private $name, $author;

  public function __construct($name, Author $author) {
    $this->name= $name;
    $this->author= $author;
  }

  public function name() { return $this->name; }

  public function author() { return $this->author; }

  public function equals($value) {
    return
      $value instanceof self &&
      $this->name === $value->name &&
      $this->author->equals($value->author)
    ;
  }
}

class Author extends \lang\Object { use \util\objects\CreateWith;
  private $name;

  public function __construct($name) {
    $this->name= $name;
  }

  public function name() { return $this->name; }
}

$address= new XmlString($xml);
$book= $address->next(new CreationOf('com.example.model.Book', [
  'name'   => function($val) { $this->name= $val->next(); },
  'author' => function($val) { $this->author= $val->next(new CreationOf('com.example.model.Author', [
    'name'   => function($val) { $this->name= $val->next() ?: '(unknown author)'; }
  ])); }
]);
```