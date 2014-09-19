<?php

namespace Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;

use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;

class Http implements VersionTransporter
{
    private $queryStringVersion;
    private $versionHeader;
    private $etags;

    public function __construct($queryStringVersion = null, $versionHeader = null, array $etags = [])
    {
        $this->queryStringVersion = $queryStringVersion;
        $this->versionHeader = $versionHeader;
        $this->etags = $etags;
    }

    public function getExpectedVersion($class, $id)
    {
        if (!empty($this->queryStringVersion)) {
            return $this->queryStringVersion;
        }

        if (!empty($this->versionHeader)) {
            return $this->versionHeader;
        }

        foreach ($this->etags as $etag) {
            if (is_int($etag)) {
                return $etag;
            }
        }

        return 1;
    }
}
