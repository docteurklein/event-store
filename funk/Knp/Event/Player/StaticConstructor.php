<?php

namespace funk\Knp\Event\Player;

use Knp\Event\Player\ReflectionBased;
use Knp\Event\Event\Generic;

class StaticConstructor implements \Funk\Spec
{
    function it_handles_static_constructors()
    {
        $player = new ReflectionBased;
        $product = $player->replay(new \ArrayIterator([new Generic('HasBeenStaticallyConstructed', ['test'])]), Product::class);

        expect($product)->toBeLike(Product::fromString('test'));
    }
}

class Product implements \Knp\Event\Emitter
{
    use \Knp\Event\Popper;

    public $name;

    public function getId()
    {
        return $this->name;
    }

    public static function fromString($name)
    {
        return new self($name);
    }

    public function getReplayableSteps()
    {
        return ['HasBeenStaticallyConstructed' => 'fromString'];
    }
}
