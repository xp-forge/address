<?php namespace util\address\unittest;

use util\Date;
use util\address\CreationOf;
use util\address\XmlFile;
use util\address\XmlResource;
use util\address\XmlStream;
use util\address\XmlString;

class XmlInputTest extends \unittest\TestCase {

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

  #[@test, @values('inputs')]
  public function feed($input) {
    $feed= $input->next(new CreationOf(Channel::with(), [
      'channel/title'       => function($iteration) { $this->title= $iteration->next(); },
      'channel/description' => function($iteration) { $this->description= $iteration->next(); },
      'channel/pubDate'     => function($iteration) { $this->pubDate= new Date($iteration->next()); },
      'channel/generator'   => function($iteration) { $this->generator= $iteration->next(); },
      'channel/link'        => function($iteration) { $this->link= $iteration->next(); },
      'channel/item'        => function($iteration) { $this->items[]= $iteration->next(new CreationOf(Item::with(), [
        'title'               => function($iteration) { $this->title= $iteration->next(); },
        'description'         => function($iteration) { $this->description= $iteration->next(); },
        'pubDate'             => function($iteration) { $this->pubDate= new Date($iteration->next()); },
        'generator'           => function($iteration) { $this->generator= $iteration->next(); },
        'link'                => function($iteration) { $this->link= $iteration->next(); },
        'guid'                => function($iteration) { $this->guid= $iteration->next(); }
      ])); }
    ]));

    $this->assertEquals(new Channel(
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