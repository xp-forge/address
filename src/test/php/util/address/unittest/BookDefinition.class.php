<?php namespace util\address\unittest;

use util\address\CreationOf;

class BookDefinition extends CreationOf {

  public function __construct() {
    parent::__construct(Book::with(), [
      'name'   => function($iteration) { $this->name= $iteration->next(); },
      'author' => function($iteration) { $this->author= $iteration->next(new CreationOf(Author::with(), [
        'name'   => function($iteration) { $this->name= $iteration->next() ?: 'Test'; }
      ])); }
    ]);
  }
}