<?php namespace util\address\unittest;

use test\{Assert, Test, Values};
use util\Date;
use util\address\{ObjectOf, XmlStreaming, XmlResource};

class XmlInputTest {

  /** @return var[][] */
  protected function inputs() {
    $package= typeof($this)->getPackage();
    return [
      [new XmlStreaming($package->getResource('releases.xml'))],
      [new XmlStreaming($package->getResourceAsStream('releases.xml')->in())],
      [new XmlStreaming($package->getResourceAsStream('releases.xml'))],
    ];
  }

  #[Test, Values(from: 'inputs')]
  public function feed($input) {
    $feed= $input->next(new ObjectOf(Channel::class, [
      'channel/title'       => function($self) { $self->title= yield; },
      'channel/description' => function($self) { $self->description= yield; },
      'channel/pubDate'     => function($self) { $self->pubDate= new Date(yield); },
      'channel/generator'   => function($self) { $self->generator= yield; },
      'channel/link'        => function($self) { $self->link= yield; },
      'channel/item'        => function($self) { $self->items[]= yield new ObjectOf(Item::class, [
        'title'               => function($self) { $self->title= yield; },
        'description'         => function($self) { $self->description= yield; },
        'pubDate'             => function($self) { $self->pubDate= new Date(yield); },
        'generator'           => function($self) { $self->generator= yield; },
        'link'                => function($self) { $self->link= yield; },
        'guid'                => function($self) { $self->guid= yield; }
      ]); }
    ]));

    Assert::equals(new Channel(
      'xp-forge/sequence releases',
      'Latest releases on Packagist of xp-forge/sequence.',
      new Date('Mon, 03 Nov 2014 20:56:13 +0000'),
      'Packagist',
      'https://packagist.org/packages/xp-forge/sequence',
      [
        new Item(
          'xp-forge/sequence (v1.0.0)',
          'Data sequences',
          new Date('Mon, 03 Nov 2014 20:56:13 +0000'),
          'https://packagist.org/packages/xp-forge/sequence',
          'xp-forge/sequence v1.0.0'
        ),
        new Item(
          'xp-forge/sequence (v0.7.4)',
          'Data sequences',
          new Date('Sat, 27 Sep 2014 13:25:19 +0000'),
          'https://packagist.org/packages/xp-forge/sequence',
          'xp-forge/sequence v0.7.4'
        )
      ]
    ), $feed);
  }
}