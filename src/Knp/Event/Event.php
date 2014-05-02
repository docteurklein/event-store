<?php

namespace Knp\Event;

use Knp\Event\Event;

interface Event
{
    public function getName();

    public function getProviderClass();
    public function getProviderId();

    public function getAttributes();
}
