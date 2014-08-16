<?php

namespace Knp\Event\Example\Shop;

//return new \Knp\Event\Serializer\AnyCallable('igbinary_serialize', 'igbinary_unserialize');

return new \Knp\Event\Serializer\Jms(
    (new \JMS\Serializer\SerializerBuilder)
        ->setSerializationVisitor('array', new \Knp\Event\Serializer\Jms\Visitor\ArraySerialize(
            new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(new \JMS\Serializer\Naming\CamelCaseNamingStrategy))
        )
        ->setDeSerializationVisitor('array', new \Knp\Event\Serializer\Jms\Visitor\ArrayDeserialize(
            new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(new \JMS\Serializer\Naming\CamelCaseNamingStrategy))
        )
        ->configureHandlers(function(\JMS\Serializer\Handler\HandlerRegistry $handlers) {
            $handlers->registerHandler('serialization', 'Rhumsaa\Uuid\Uuid', 'array', function($visitor, \Rhumsaa\Uuid\Uuid $id, array $type) {
                return (string) $id;
            });
            $handlers->registerHandler('deserialization', 'Rhumsaa\Uuid\Uuid', 'array', function($visitor, $id, array $type) {
                return \Rhumsaa\Uuid\Uuid::fromString($id);
            });
            $handlers->registerHandler('serialization', 'DateTime', 'array', function($visitor, \DateTime $date, array $type) {
                return $date->format(\DateTime::ISO8601);
            });
            $handlers->registerHandler('deserialization', 'DateTime', 'array', function($visitor, $date, array $type) {
                return \DateTime::createFromFormat(\DateTime::ISO8601, $date);
            });
            $handlers->registerSubscribingHandler(new \Knp\Event\Serializer\Jms\Handler\Event\Generic);
        })
        ->addDefaultHandlers()
        ->addDefaultListeners()
    ->build()
);
