<?php

namespace Knp\Event\Store;

use Knp\Event\Store;

interface IsVersioned extends Store
{
    public function getCurrentVersion($class, $id);
}
