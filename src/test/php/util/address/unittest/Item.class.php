<?php namespace util\address\unittest;

use util\Date;
use util\Objects;
use lang\partial\WithCreation;

class Item extends \lang\Object {
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

  /** @return string */
  public function title() { return $this->title; }

  /** @return string */
  public function description() { return $this->description; }

  /** @return util.Date */
  public function pubDate() { return $this->pubDate; }

  /** @return string */
  public function link() { return $this->link; }

  /** @return string */
  public function guid() { return $this->guid; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return $this->getClassName().'@'.Objects::stringOf(get_object_vars($this));
  }

  /**
   * Checks for equality
   *
   * @param  var $value
   * @return bool
   */
  public function equals($value) {
    return $value instanceof self && (
      $this->title === $value->title &&
      $this->description === $value->description &&
      $this->pubDate->equals($value->pubDate) &&
      $this->link === $value->link &&
      $this->guid === $value->guid
    );
  }
}