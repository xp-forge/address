Address
=======

[![Build status on GitHub](https://github.com/xp-forge/address/workflows/Tests/badge.svg)](https://github.com/xp-forge/address/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/address/version.svg)](https://packagist.org/packages/xp-forge/address)

Creates objects from XML input streams while parsing them. Yes, this still happens today 😉

Example
-------
Given the following two value objects:

```php
class Book {
  public $name, $author;

  public function __construct(string $name, Author $author) {
    $this->name= $name;
    $this->author= $author;
  }
}

class Author {
  public $name;

  public function __construct(string $name) {
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
use util\address\{XmlStreaming, ObjectOf};

$socket= /* ... */

$stream= new XmlStreaming($socket);
$book= $stream->next(new ObjectOf(Book::class, [
  'name'   => fn($self) => $self->name= yield,
  'author' => fn($self) => $self->author= yield new ObjectOf(Author::class, [
    'name'   => fn($self) => $self->name= yield ?: '(unknown author)'; }
  ])
]);
```

Creating values
---------------
Definitions are used to create structured data from the XML input. Here are all the implementations:

### ValueOf

Simplemost version which is given a seed value, which it can modify through the given address functions.

```php
use util\address\{XmlStreaming, ValueOf};

// Parse into string 'Tim Taylor'
$stream= new XmlStreaming('<name>Tim Taylor</name>');
$name= $stream->next(new ValueOf(null, [
  '.' => fn(&$self) => $self= yield,
]);

// Parse into array ['More', 'Power']
$stream= new XmlStreaming('<tools><tool>More</tool><tool>Power</tool></tools>');
$name= $stream->next(new ValueOf([], [
  'tool' => fn(&$self) => $self[]= yield,
]);

// Parse into map ['id' => 6100, 'name' => 'more power']
$stream= new XmlStreaming('<tool id="6100">more power</tool>');
$book= $stream->next(new ValueOf([], [
  '@id' => fn(&$self) => $self['id']= (int)yield,
  '.'   => fn(&$self) => $self['name']= yield,
]);
```

### ObjectOf

Creates objects without invoking their constructors. Modifies the members directly, including non-public ones.

```php
use util\address\{XmlStreaming, ObjectOf};

class Book {
  public $isbn, $name;
}

// Parse into Book(isbn: '978-0552151740', name: 'A Short History...')
$stream= new XmlStreaming('<book isbn="978-0552151740"><name>A Short History...</name></book>');
$book= $stream->next(new ObjectOf(Book::class, [
  '@isbn' => fn($self) => $self->isbn= yield,
  'name'  => fn($self) => $self->name= yield,
]);
```

### RecordOf

Works with *record* classes, which are defined as being immutable and having an all-arg constructor. Modifies the named constructor arguments.

```php
use util\address\{XmlStreaming, RecordOf};

class Book {
  public function __construct(private string $isbn, private string $name) { }

  public function isbn() { return $this->isbn; }
  public function name() { return $this->name; }
}

// Parse into Book(isbn: '978-0552151740', name: 'A Short History...')
$stream= new XmlStreaming('<book isbn="978-0552151740"><name>A Short History...</name></book>');
$book= $stream->next(new RecordOf(Book::class, [
  '@isbn' => fn(&$args) => $args['isbn']= yield,
  'name'  => fn(&$args) => $args['name']= yield,
]);
```

Iteration
---------
Any `Address` instance can be iterated using the `foreach` statement. Using the [data sequences library](https://github.com/xp-forge/sequence) in combination with calling the `value()` method here's a way to parse an RSS feed's items:

```php
use peer\http\HttpConnection;
use util\data\Sequence;
use util\Date;
use util\address\{XmlStream, ObjectOf};
use util\cmd\Console;

class Item {
  public $title, $description, $pubDate, $generator, $link, $guid;
}

$definition= new ObjectOf(Item::class, [
  'title'       => fn($self) => $self->title= yield,
  'description' => fn($self) => $self->description= yield,
  'pubDate'     => fn($self) => $self->pubDate= new Date(yield),
  'generator'   => fn($self) => $self->generator= yield,
  'link'        => fn($self) => $self->link= yield,
  'guid'        => fn($self) => $self->guid= yield,
]);

$conn= new HttpConnection('https://www.tagesschau.de/xml/rss2/');
$stream= new XmlStream($conn->get()->in());

Sequence::of($stream->pointers('//channel/item'))
  ->map(fn($pointer) => $pointer->value($definition))
  ->each(fn($item) => Console::writeLine('- ', $item->title, "\n  ", $item->link))
;
```