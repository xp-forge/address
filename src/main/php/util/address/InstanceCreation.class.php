<?php namespace util\address;

use lang\{Reflection, ClassLoader};

/** Base class for instance creation */
abstract class InstanceCreation {

  /** @param string|lang.XPClass $type */
  public static function of($type): self {
    $reflect= Reflection::of($type);
    $name= 'InstanceCreation·'.str_replace('.', '·', $reflect->name());
    if (class_exists($name, false)) return new $name();

    // Generate instance members and constructor arguments
    $members= $arguments= '';
    if ($constructor= $reflect->constructor()) {
      foreach ($constructor->parameters() as $p) {
        $members.= 'public $'.$p->name().($p->optional() ? '= '.var_export($p->default(), true) : '').';';
        $arguments.= ', $this->'.$p->name();
      }
      $arguments= substr($arguments, 2);
    }
    $class= ClassLoader::defineClass($name, self::class, [], "{
      {$members}

      /** Creates an instance */
      public function create(): {$reflect->literal()} {
        return new {$reflect->literal()}({$arguments});
      }
    }");
    return $class->newInstance();
  }

  /** @return object */
  public abstract function create();
}