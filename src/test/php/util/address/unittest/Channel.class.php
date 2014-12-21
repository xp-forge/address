<?php namespace util\address\unittest;

use util\Date;
use util\Objects;

class Channel extends \lang\Object { use \util\objects\CreateWith;
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

  /** @return string */
  public function title() { return $this->title; }

  /** @return string */
  public function description() { return $this->description; }

  /** @return util.Date */
  public function pubDate() { return $this->pubDate; }

  /** @return string */
  public function generator() { return $this->generator; }

  /** @return string */
  public function link() { return $this->link; }

  /** @return util.data.unittest.Item[] */
  public function items() { return $this->items; }

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
      $this->generator === $value->generator &&
      $this->link === $value->link &&
      Objects::equal($this->items, $value->items)
    );
  }
}