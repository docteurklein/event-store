<?php

namespace Knp\Event\Store\Concurrency\Optimistic\VersionTransporter\Symfony;

use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class Aggregate implements VersionTransporter
{
    public function __construct(array $transporters)
    {
        $this->transporters = $transporters;
    }

    public function getExpectedVersion($class, $id)
    {
        foreach ($this->transporters as $transporter) {
            $version = $transporter->getExpectedVersion($class, $id);
            if (!empty($version)) {
                return $version;
            }
        }
    }

    public function update($class, $id, $version)
    {
        foreach ($this->transporters as $transporter) {
            $transporter->update($class, $id, $version);
        }
    }
}
