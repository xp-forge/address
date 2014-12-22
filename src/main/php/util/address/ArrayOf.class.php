<?php namespace util\address;

/**
 * Creates an array of elements based on a given component definition
 *
 * @test  xp://util.address.unittest.ArrayOfTest
 */
class ArrayOf extends \lang\Object implements Definition {
  private $component, $match;

  /**
   * Creates a new array definition
   *
   * @param  util.address.Definition $component
   * @param  string[] $match Optional relative paths to match, using NULL will match any
   */
  public function __construct(Definition $component= null, $match= null) {
    $this->component= $component;
    $this->match= $match;
  }

  /**
   * Creates a value from a given iteration
   *
   * @param  util.address.Iteration $iteration
   * @return var
   */
  public function create($iteration) {
    if ($this->match) {
      $match= array_map(function($path) use($iteration) { return $iteration->base().$path; }, $this->match);
    } else {
      $match= [$iteration->path()];
    }

    $r= [];
    while (in_array($iteration->path(), $match, true)) {
      $r[]= $iteration->next($this->component);
    }

    return $r;
  }
}