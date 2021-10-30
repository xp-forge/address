Address
=======

[![Build status on GitHub](https://github.com/xp-forge/address/workflows/Tests/badge.svg)](https://github.com/xp-forge/address/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/address/version.png)](https://packagist.org/packages/xp-forge/address)

Creates objects from XML input streams while parsing them.

Example
-------
Given the following two value objects:

```php
class Book {
  private $name, $author;

  public function __construct($name, Author $author) {
    $this->name= $name;
    $this->author= $author;
  }
}

class Author {
  private $name;

  public function __construct($name) {
    $this->name= $name;
  }
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
use util\address\{XmlStream, ObjectOf};

$socket= /* ... */

$address= new XmlStream($socket->in());
$book= $address->next(new ObjectOf(Book::class, [
  'name'   => function($self, $it) { $self->name= $it->next(); },
  'author' => function($self, $it) { $self->author= $it->next(new ObjectOf(Author::class, [
    'name'   => function($self, $it) { $self->name= $it->next() ?: '(unknown author)'; }
  ])); }
]);
```

Creating values
---------------
Definitions are used to create structured data from the XML input. Here are all the implementations:

### ObjectOf

Creates objects without invoking their constructors. Modifies the members directly, including non-public ones.

```php
use util\address\{XmlString, ObjectOf};

class Book {
  public $isbn, $name;
}

$xml= new XmlString('<book isbn="978-0552151740"><name>A Short History...</name></book>');
$book= $xml->next(new ObjectOf(Book::class, [
  '@isbn' => function($self, $it) { $self->isbn= $it->next(); },
  'name'  => function($self, $it) { $self->name= $it->next(); },
]);
```

### MapOf

Much like *ObjectOf*, but creates a map instead of an object.

```php
use util\address\{XmlString, MapOf};

$xml= new XmlString('<book isbn="978-0552151740"><name>A Short History...</name></book>');
$book= $xml->next(new MapOf(
  '@isbn' => function(&$self, $it) { $self['isbn']= $it->next(); },
  'name'  => function(&$self, $it) { $self['name']= $it->next(); },
]);
```

### RecordOf

Works with *record* classes, which are defined as having an all-arg constructor. Modifies the named constructor arguments.

```php
use util\address\{XmlString, RecordOf};

class Book {
  public function __construct(private $isbn, private $name) { }
}

$xml= new XmlString('<book isbn="978-0552151740"><name>A Short History...</name></book>');
$book= $xml->next(new RecordOf(Book::class, [
  '@isbn' => function(&$args, $it) { $args['isbn']= $it->next(); },
  'name'  => function(&$args, $it) { $args['name']= $it->next(); },
]);
```

Iteration
---------
Any `Address` instance can be iterated using the `foreach` statement. Using the [data sequences library](https://github.com/xp-forge/sequence) in combination with calling the `next()` method here's a way to parse an RSS feed's items:

```php
use peer\http\HttpConnection;
use util\data\Sequence;
use util\Date;
use util\address\{XmlStream, ObjectOf};
use util\cmd\Console;

$conn= new HttpConnection('https://www.tagesschau.de/xml/rss2/');
$stream= new XmlStream($conn->get()->in());

Sequence::of($stream)
  ->filter(fn($value, $path) => '//channel/item' === $path)
  ->map(fn() => $stream->next(new ObjectOf(Item::class, [
    'title'       => fn($self, $it) => $self->title= $it->next(),
    'description' => fn($self, $it) => $self->description= $it->next(),
    'pubDate'     => fn($self, $it) => $self->pubDate= new Date($it->next()),
    'generator'   => fn($self, $it) => $self->generator= $it->next(),
    'link'        => fn($self, $it) => $self->link= $it->next(),
    'guid'        => fn($self, $it) => $self->guid= $it->next(),
  ])))
  ->each(fn($item) => Console::writeLine('- ', $item->title, "\n  ", $item->link))
;
```