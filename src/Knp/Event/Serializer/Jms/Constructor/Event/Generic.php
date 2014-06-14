<?php

namespace Knp\Event\Serializer\Jms\Constructor\Event;

use JMS\Serializer\VisitorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Construction\ObjectConstructorInterface;

class Generic implements ObjectConstructorInterface
{
    public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context)
    {
        return (new \ReflectionClass($type['name']))->newInstanceWithoutConstructor();
    }
}
