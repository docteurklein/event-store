<?php

namespace Knp\Event\Serializer\Jms\Visitor;

use JMS\Serializer\GenericDeserializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Context;

final class ArrayDeserialize extends GenericDeserializationVisitor
{
    public function decode($str)
    {
        return $str;
    }

    public function getResult()
    {
        return null;
    }
}
