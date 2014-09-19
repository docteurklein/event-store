<?php

namespace Knp\Event\Store\Concurrency\Optimistic;

interface VersionTransporter
{
    public function getExpectedVersion($class, $id);
}
