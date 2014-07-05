<?php

namespace Knp\Event\Serializer\Jms\Visitor;

use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Context;

class ArraySerialize extends JsonSerializationVisitor
{
    public function getResult()
    {
        return $this->getRoot();
    }
}
