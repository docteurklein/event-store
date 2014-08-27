<?php

namespace example\Shop\Model;

use JMS\Serializer\Annotation as Serialize;

class Price
{
    private $currency;
    private $value;

    public function __construct($currency, $value)
    {
        $this->currency = $currency;
        $this->value = $value;
    }

    public function __toString()
    {
        return sprintf('%s %s', $this->currency, $this->value);
    }
}

