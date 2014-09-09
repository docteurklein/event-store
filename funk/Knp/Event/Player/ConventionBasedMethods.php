<?php

namespace funk\Knp\Event\Player;

use Knp\Event\Player\ReflectionBased;
use Knp\Event\Event\Generic;

class ConventionBasedMethods implements \Funk\Spec
{
    function it_uses_apply_methods_if_as_a_fallback()
    {
        $player = new ReflectionBased;
        $product = $player->replay(new \ArrayIterator([new Generic('HasBeenCreated', ['test'])]), ProductWithApply::class);

        expect($product->name)->toBe('test');
    }
}

class ProductWithApply implements \Knp\Event\Emitter
{
    use \Knp\Event\Popper;

    public $name;

    public function getId()
    {
        return $this->name;
    }

    public function applyHasBeenCreated($name)
    {
        $this->name = $name;
    }

    public function getReplayableSteps()
    {
        return [];
    }
}
