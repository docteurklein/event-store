<?php

namespace Knp\Event\Event;

use Knp\Event\Event;
use Knp\Event\Provider;
use Doctrine\Common\EventArgs;

class Generic extends EventArgs implements Event, \Serializable
{
    private $name;
    private $attributes = [];
    private $providerClass;
    private $providerId;

    public function __construct($name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function setProvider(Provider $provider)
    {
        $this->providerClass = get_class($provider);
        $this->providerId = $provider->getId();
    }

    public function setProviderClass($class)
    {
        $this->providerClass = $class;
    }

    public function setProviderId($id)
    {
        $this->providerId = $id;
    }

    public function getProviderClass()
    {
        return $this->providerClass;
    }

    public function getProviderId()
    {
        return $this->providerId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function __get($index)
    {
        return $this->attributes[$index];
    }

    public function serialize()
    {
        return serialize([
            $this->name,
            $this->attributes,
            $this->providerClass,
            $this->providerId,
        ]);
    }

    public function unserialize($data)
    {
        list(
            $this->name,
            $this->attributes,
            $this->providerClass,
            $this->providerId,
        ) = unserialize($data);
    }
}
