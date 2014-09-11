<?php

namespace Knp\Event\Store;

interface IsVersioned
{
    public function getCurrentVersion($class, $id);
}
