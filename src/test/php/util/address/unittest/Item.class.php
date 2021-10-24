<?php namespace util\address\unittest;

use lang\Value;
use util\{Date, Objects};

class Item implements Value {
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

  /** @return string */
  public function hashCode() { return 'C'.Objects::hashOf((array)$this); }

  /** @return string */
  public function toString() { return nameof($this).'@'.Objects::stringOf(get_object_vars($this)); }

  /**
   * Compares this
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare((array)$this, (array)$value) : 1;
  }
}