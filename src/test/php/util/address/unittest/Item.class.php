<?php namespace util\address\unittest;

use util\Date;
use util\Objects;
use lang\partial\WithCreation;
use lang\partial\ValueObject;

class Item extends \lang\Object {
  use Item\including\WithCreation;
  use Item\including\ValueObject;

  private $title, $description, $pubDate, $link, $guid;

  /**
   * Creates a new feed
   *
   * @param  string $name
   * @param  string $description
   * @param  util.Date $pubDate
   * @param  string $link
   * @param  string $guid
   */
  public function __construct($title, $description, Date $pubDate, $link, $guid) {
    $this->title= $title;
    $this->description= $description;
    $this->pubDate= $pubDate;
    $this->link= $link;
    $this->guid= $guid;
  }
}