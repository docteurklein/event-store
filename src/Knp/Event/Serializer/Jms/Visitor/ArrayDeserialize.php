<?php

namespace Knp\Event\Serializer\Jms\Visitor;

use JMS\Serializer\GenericDeserializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Context;

class ArrayDeserialize extends GenericDeserializationVisitor
{
    public function decode($str)
    {
        return $str;
    }
}
