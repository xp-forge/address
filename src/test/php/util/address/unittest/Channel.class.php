<?php namespace util\address\unittest;

use util\Date;
use util\Objects;
use lang\partial\WithCreation;
use lang\partial\ValueObject;

class Channel extends \lang\Object {
  use Channel\including\WithCreation;
  use Channel\including\ValueObject;

  private $title, $description, $pubDate, $generator, $link, $items;

  /**
   * Creates a new feed
   *
   * @param  string $name
   * @param  string $description
   * @param  util.Date $pubDate
   * @param  string $generator
   * @param  string $link
   * @param  util.data.unittest.Item[] $items
   */
  public function __construct($title, $description, Date $pubDate, $generator, $link, $items) {
    $this->title= $title;
    $this->description= $description;
    $this->pubDate= $pubDate;
    $this->generator= $generator;
    $this->link= $link;
    $this->items= $items;
  }
}