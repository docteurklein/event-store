<?php

namespace Knp\Event\Serializer\Jms\Listener\Event;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;

class Generic implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [ 'event' => 'serializer.pre_deserialize', 'method' => 'onPreDeserialize' ],
        ];
    }

    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $data = $event->getData();
        if (isset($data['__type__'])) {
            $event->setType($data['__type__']);
            unset($data['__type__']);
            $event->setData($data);
        }
    }
}
