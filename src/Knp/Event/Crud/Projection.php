<?php

namespace Knp\Event\Crud;

use Doctrine\DBAL\Connection;
use Knp\Event\Subscriber;
use Knp\Event\Event;
use Knp\Event\Emitter;

class Projection implements Subscriber
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
            'Created',
            'Updated',
            'Deleted',
        ];
    }

    public function Created(Event $event)
    {
        $this->ensureSchema($this->getTableName($event->getEmitterClass()), $event->getAttributes());
        $this->connection->insert(
            $this->getTableName($event->getEmitterClass()),
            array_map(function($value) {
                return $value instanceof Emitter ? $value->getId() : $value;
            }, $event->getAttributes())
        );
    }

    public function Updated(Event $event)
    {
        $this->ensureSchema($this->getTableName($event->getEmitterClass()), $event->getAttributes()['changeSet']);
        $this->connection->update(
            $this->getTableName($event->getEmitterClass()),
            array_map(function($value) {
                return $value instanceof Emitter ? $value->getId() : $value;
            }, $event->getAttributes()['changeSet']),
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
            $table = $schema->createTable($name);
        }
        $table = $schema->getTable($name);
        foreach ($columns as $property => $value) {
            if (!$table->hasColumn($property)) {
                $table->addColumn($property, 'text', ['notnull' => false]);
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
}
