<?php

namespace Knp\Event\Crud;

use Knp\Event\Event;

trait Emitter
{
    private $changeSet;

    public function changeSet(callable $change)
    {
        $referent = clone $this;
        $change();
        $changes = [];
        foreach ($this as $property => $newValue) {
            if ($referent->$property !== $newValue) {
                $changes[$property] = $newValue;
            }
        }

        return $changes;
    }

    public function track(callable $change)
    {
        $changes = $this->changeSet($change);
        $attributes = ['changeSet' => array_merge($this->changeSet ? $this->changeSet->getAttributes()['changeSet'] : [], $changes)];
        if ($key = array_search($this->changeSet, $this->events, true)) {
            $this->events[$key] = $this->changeSet = new Event\Generic('Updated', $attributes);
        }
        else {
            $this->events[] = $this->changeSet = new Event\Generic('Updated', $attributes);
        }
        $this->changeSet->setEmitter($this);
    }

    public function restoreState($changeSet)
    {
        foreach ($changeSet as $property => $value) {
            $this->$property = $value;
        }
    }

    public function delete()
    {
        $this->emit(new Event\Generic('Deleted'));
    }

    abstract public function emit(Event $event);
}
