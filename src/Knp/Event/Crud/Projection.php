<?php

namespace Knp\Event\Crud;

use Doctrine\DBAL\Connection;
use Knp\Event\Subscriber;
use Knp\Event\Event;
use Knp\Event\Emitter;
use Doctrine\DBAL\Types\Type;

final class Projection implements Subscriber
{
    private $connection;
    private $tableNames;

    public function __construct(Connection $connection, array $tableNames = [])
    {
        $this->connection = $connection;
        $this->tableNames = $tableNames;
    }

    public function getSubscribedEvents()
    {
        return [
            'Created' => 'Created',
            'Updated' => 'Updated',
            'Deleted' => 'Deleted',
        ];
    }

    public function Created(Event $event)
    {
        $attributes = array_map(function($value) {
            return $value instanceof Emitter ? $value->getId() : $value;
        }, $event->getAttributes());

        $this->ensureSchema($this->getTableName($event->getEmitterClass()), $attributes);
        $this->connection->insert(
            $this->getTableName($event->getEmitterClass()),
            $attributes
        );
    }

    public function Updated(Event $event)
    {
        $attributes = array_map(function($value) {
            return $value instanceof Emitter ? $value->getId() : $value;
        }, $event->getAttributes()['changeSet']);

        $this->ensureSchema($this->getTableName($event->getEmitterClass()), $attributes);
        $this->connection->update(
            $this->getTableName($event->getEmitterClass()),
            $attributes,
            ['id' => $event->getEmitterId()]
        );
    }

    public function Deleted(Event $event)
    {
        $this->connection->delete($this->getTableName($event->getEmitterClass()), ['id' => $event->getEmitterId()]);
    }

    private function ensureSchema($name, array $columns = [])
    {
        $schema = $this->connection->getSchemaManager()->createSchema();
        if (!$schema->hasTable($name)) {
            $schema->createTable($name);
        }
        $table = $schema->getTable($name);
        foreach ($columns as $property => $value) {
            if (!$table->hasColumn($property)) {
                $table->addColumn($property, $this->getType($value), ['notnull' => false]);
            }
        }

        $sync = new \Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer($this->connection);
        $sync->updateSchema($schema, true);
    }

    private function getTableName($class)
    {
        if (isset($this->tableNames[$class])) {
            return $this->tableNames[$class];
        }

        return str_replace('\\', '_', $class);
    }

    private function getType($value)
    {
        try {
            return Type::getType(gettype($value))->getName();
        }
        catch (\Exception $e) {
            return 'text';
        }
    }
}
