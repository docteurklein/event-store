<?php

namespace PhpSpec\Iterator;

use PhpSpec\Wrapper\Collaborator as Base;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Promise\ReturnPromise;

class Collaborator extends Base
{
    private $prophecy;

    public function __construct(ObjectProphecy $prophecy, $class)
    {
        parent::__construct($prophecy);
        $this->prophecy  = $prophecy;
        $this->beADoubleOf($class);
    }

    public function iterates(array $elements)
    {
        $this->prophecy->valid()->will(new ReturnPromise(array_merge(array_fill(0, count($elements), true), [false])));
        $this->prophecy->count()->willReturn(count($elements));
        $this->prophecy->current()->will(new ReturnPromise($elements));
        $this->prophecy->next()->willReturn();
        $this->prophecy->rewind()->willReturn();
    }
}
