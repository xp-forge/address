<?php namespace util\address\unittest;

use unittest\Assert;
use unittest\{Test, Values};
use util\Date;
use util\address\{ObjectOf, XmlFile, XmlResource, XmlStream, XmlString};

class XmlInputTest {

  /** @return var[][] */
  protected function inputs() {
    $t= typeof($this);
    return [
      [new XmlString($t->getPackage()->getResource('releases.xml'))],
      [new XmlStream($t->getPackage()->getResourceAsStream('releases.xml')->in())],
      [new XmlFile($t->getPackage()->getResourceAsStream('releases.xml'))],
      [new XmlResource($t, 'releases.xml')]
    ];
  }

  #[Test, Values('inputs')]
  public function feed($input) {
    $feed= $input->next(new ObjectOf(Channel::class, [
      'channel/title'       => function($self, $it) { $self->title= $it->next(); },
      'channel/description' => function($self, $it) { $self->description= $it->next(); },
      'channel/pubDate'     => function($self, $it) { $self->pubDate= new Date($it->next()); },
      'channel/generator'   => function($self, $it) { $self->generator= $it->next(); },
      'channel/link'        => function($self, $it) { $self->link= $it->next(); },
      'channel/item'        => function($self, $it) { $self->items[]= $it->next(new ObjectOf(Item::class, [
        'title'               => function($self, $it) { $self->title= $it->next(); },
        'description'         => function($self, $it) { $self->description= $it->next(); },
        'pubDate'             => function($self, $it) { $self->pubDate= new Date($it->next()); },
        'generator'           => function($self, $it) { $self->generator= $it->next(); },
        'link'                => function($self, $it) { $self->link= $it->next(); },
        'guid'                => function($self, $it) { $self->guid= $it->next(); }
      ])); }
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