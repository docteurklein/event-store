<?php

namespace Knp\Event\Store\Concurrency\Optimistic\VersionTransporter\Symfony;

use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

final class Request implements VersionTransporter, EventSubscriberInterface
{
    private $request;
    private $key;
    private $versions = [];

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function __construct(SymfonyRequest $request, $key = 'version')
    {
        $this->request = $request;
        $this->key = $key;
    }

    public function getExpectedVersion($class, $id)
    {
        return $this->request->query->get($this->key, $this->request->headers->get($this->key, current($this->request->getEtags())));
    }

    public function update($class, $id, $version)
    {
        $this->versions[$class][$id] = $version;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->versions)) as $version) {
            $event->getResponse()->setEtag($version);
            $event->getResponse()->headers->set($this->key, $version);
        }
    }
}
