<?php namespace util\address\unittest;

use test\{Assert, Test, Values};
use util\address\{JsonStreaming, ObjectOf, ValueOf};

class JsonInputTest {

  /** @return var[][] */
  protected function inputs() {
    $package= typeof($this)->getPackage();
    return [
      [new JsonStreaming($package->getResource('composer.json'))],
      [new JsonStreaming($package->getResourceAsStream('composer.json')->in())],
      [new JsonStreaming($package->getResourceAsStream('composer.json'))]
    ];
  }

  #[Test, Values(from: 'inputs')]
  public function feed($input) {
    $composer= $input->next(new ObjectOf(Composer::class, [
      'name'        => function($self) { $self->name= yield; },
      'type'        => function($self) { $self->type= yield; },
      'keywords/[]' => function($self) { $self->keywords[]= yield; },
      'require'     => function($self) { $self->requirements= yield new ValueOf([], [
        '*' => function(&$self, $path) { $self[$path]= yield; }
      ]); }
    ]));

    Assert::equals(
      new Composer('xp-forge/address', 'library', ['module', 'xp'], [
        'xp-framework/core'       => '^11.0 | ^10.0',
        'xp-framework/reflection' => '^2.0 | ^1.9',
        'xp-framework/tokenize'   => '^9.0 | ^8.1',
        'php'                     => '>=7.0.0',
      ]),
      $composer
    );
  }
}