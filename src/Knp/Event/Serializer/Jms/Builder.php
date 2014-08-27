<?php

namespace Knp\Event\Serializer\Jms;

use Knp\Event\Serializer\Jms;
use JMS\Serializer\SerializerBuilder;
use Knp\Event\Serializer\Jms\Visitor\ArraySerialize;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use Knp\Event\Serializer\Jms\Visitor\ArrayDeserialize;
use JMS\Serializer\Handler\HandlerRegistry;
use Rhumsaa\Uuid\Uuid;
use DateTime;
use Knp\Event\Serializer\Jms\Handler\Event\Generic;

class Builder
{
    public function configure(SerializerBuilder $builder)
    {
        return $builder
            ->setSerializationVisitor('array', new ArraySerialize(
                new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy))
            )
            ->setDeSerializationVisitor('array', new ArrayDeserialize(
                new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy))
            )
            ->configureHandlers(function(HandlerRegistry $handlers) {
                $handlers->registerHandler('serialization', 'Rhumsaa\Uuid\Uuid', 'array', function($visitor, Uuid $id, array $type) {
                    return (string) $id;
                });
                $handlers->registerHandler('deserialization', 'Rhumsaa\Uuid\Uuid', 'array', function($visitor, $id, array $type) {
                    return \Rhumsaa\Uuid\Uuid::fromString($id);
                });
                $handlers->registerHandler('serialization', 'DateTime', 'array', function($visitor, DateTime $date, array $type) {
                    return $date->format(DateTime::ISO8601);
                });
                $handlers->registerHandler('deserialization', 'DateTime', 'array', function($visitor, $date, array $type) {
                    return DateTime::createFromFormat(DateTime::ISO8601, $date);
                });
                $handlers->registerSubscribingHandler(new Generic);
            })
            ->addDefaultHandlers()
            ->addDefaultListeners()
        ;
    }

    public function build()
    {
        return new Jms($this->configure(new SerializerBuilder)->build());
    }
}
