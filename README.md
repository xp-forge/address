Creation
========

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/address.svg)](http://travis-ci.org/xp-forge/address)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)

Creates objects from XML input streams while parsing them.

Example
-------
Given the following two value objects:
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
}

class Author extends \lang\Object { use \util\objects\CreateWith;
  private $name;

  public function __construct($name) {
    $this->name= $name;
  }

  public function name() { return $this->name; }
}
```

...and this XML:

```xml
<?xml version="1.0" encoding="utf-8"?>
<book>
  <name>A Short History of Nearly Everything</name>
  <author>
    <name>Bill Bryson</name>
  </author>
</book>
```

...the following will map the XML to an object instance while reading it from the socket.

```php
<?php namespace com\example\input;

use util\address\XmlString;
use com\example\model\Book;
use com\example\model\Author;

// ...

$address= new XmlStream($socket->in());
$book= $address->next(new CreationOf(Book::with(), [
  'name'   => function($val) { $this->name= $val->next(); },
  'author' => function($val) { $this->author= $val->next(new CreationOf(Author::with(), [
    'name'   => function($val) { $this->name= $val->next() ?: '(unknown author)'; }
  ])); }
]);
```