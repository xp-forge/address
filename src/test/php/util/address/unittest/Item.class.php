<?php namespace util\address\unittest;

use util\Date;
use lang\partial\ValueObject;
use lang\partial\WithCreation;

class Item extends \lang\Object {
  use Item\including\ValueObject;
  use Item\including\WithCreation;

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