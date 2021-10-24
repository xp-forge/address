<?php namespace util\address\unittest;

use util\address\ObjectOf;

class BookDefinition extends ObjectOf {

  public function __construct() {
    parent::__construct(Book::class, [
      'name'   => function($iteration) { $this->name= $iteration->next(); },
      'author' => function($iteration) { $this->author= $iteration->next(new ObjectOf(Author::class, [
        'name'   => function($iteration) { $this->name= $iteration->next() ?: 'Test'; }
      ])); }
    ]);
  }
}