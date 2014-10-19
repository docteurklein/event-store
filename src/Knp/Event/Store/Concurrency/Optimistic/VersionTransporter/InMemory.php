<?php

namespace Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;

use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;

final class InMemory implements VersionTransporter
{
    private $default;
    private $versions = [];

    public function __construct($default = 1)
    {
        $this->default = $default;
    }

    public function getExpectedVersion($class, $id)
    {
        if (!empty($this->versions[$class][$id])) {
            return $this->versions[$class][$id];
        }

        return $this->default;
    }

    public function update($class, $id, $version)
    {
        $this->versions[$class][$id] = $version;
    }
}
