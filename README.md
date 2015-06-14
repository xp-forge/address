Creation
========

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/address.svg)](http://travis-ci.org/xp-forge/address)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Supports HHVM 3.5+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_5plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/address/version.png)](https://packagist.org/packages/xp-forge/address)

Creates objects from XML input streams while parsing them.

Example
-------
Given the following two value objects:

```php
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
$socket= /* ... */

$address= new XmlStream($socket->in());
$book= $address->next(new CreationOf(Book::with(), [
  'name'   => function($val) { $this->name= $val->next(); },
  'author' => function($val) { $this->author= $val->next(new CreationOf(Author::with(), [
    'name'   => function($val) { $this->name= $val->next() ?: '(unknown author)'; }
  ])); }
]);
```

Iteration
---------
Any `Address` instance can be iterated using the `foreach` statement. Using the [data sequences library](https://github.com/xp-forge/sequence) in combination with calling the `next()` method here's a way to parse an RSS feed's items:

```php
$conn= new HttpConnection('http://www.tagesschau.de/xml/rss2');
$stream= new XmlStream($conn->get()->in());

Sequence::of($stream)
  ->filter(function($value, $path) { return '//channel/item' === $path; })
  ->map(function() use($stream) { return $stream->next(new CreationOf(Item::with(), [
    'title'       => function($val) { $this->title= $val->next(); },
    'description' => function($val) { $this->description= $val->next(); },
    'pubDate'     => function($val) { $this->pubDate= new Date($val->next()); },
    'generator'   => function($val) { $this->generator= $val->next(); },
    'link'        => function($val) { $this->link= $val->next(); },
    'guid'        => function($val) { $this->guid= $val->next(); }
  ])); })
  ->each(function($item) {
    Console::writeLine('- ', $item->title());
    Console::writeLine('  ', $item->link());
  })
;
```