<?php namespace util\address\unittest;

use util\address\ObjectOf;

class BookDefinition extends ObjectOf {

  public function __construct() {
    parent::__construct(Book::class, [
      'name'   => function($self, $it) { $self->name= $it->next(); },
      'author' => function($self, $it) { $self->author= $it->next(new ObjectOf(Author::class, [
        'name'   => function($self, $it) { $self->name= $it->next() ?: 'Test'; }
      ])); }
    ]);
  }
}