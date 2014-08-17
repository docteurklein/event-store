<?php

namespace Knp\Event\Example\Crud;

require __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();

$evm = new \Doctrine\Common\EventManager;
$conn = \Doctrine\DBAL\DriverManager::getConnection([
        'dbname' => 'event_store_projection',
        'user' => 'florian',
        'password' => null,
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
    ]
);

$evm->addEventSubscriber(new \Knp\Event\Crud\Projection($conn));

$repository = new \Knp\Event\Repository(
    new \Knp\Event\Store\Dispatcher(
        new \Knp\Event\Store\Logger(new \Knp\Event\Store\InMemory),
        $evm
    ),
    new \Knp\Event\Player\Aggregate(
        ['Knp\Event\Example\Shop\roduct' => new \Knp\Event\Player\ReflectionBased],
        new \Knp\Event\Player\ReflectionBased
    )
);

class Address implements \Knp\Event\Emitter
{
    use \Knp\Event\Popper,
        \Knp\Event\Crud\Emitter
    ;

    private $id;
    private $street;
    private $number;
    private $city;
    private $country;
    private $previousOne;

    public function __construct($number, $street, $city, $country, Address $previousOne = null)
    {
        $this->emit(new \Knp\Event\Event\Generic('Created', $c = $this->changeSet(function() use($number, $street, $city, $country, $previousOne) {
            $this->previousOne = $previousOne;
            $this->id      = \Rhumsaa\Uuid\Uuid::uuid4();
            $this->number  = $number;
            $this->street  = $street;
            $this->city    = $city;
            $this->country = $country;
        })));
    }

    public function getId()
    {
        return $this->id;
    }

    public function getReplayableSteps()
    {
        return [
            'Created' => 'restoreState',
            'Updated' => 'restoreState',
        ];
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->track(function() use($street) {
            $this->street = $street;
        });
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->track(function() use($number) {
            $this->number = $number;
        });
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->track(function() use($city) {
            $this->city = $city;
        });
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->track(function() use($country) {
            $this->country = $country;
        });
    }

    public function setPreviousOne(Address $previousOne)
    {
        $this->track(function() use($previousOne) {
            $this->previousOne = $previousOne;
        });
    }
}

$address = new Address(16, 'rue des erables', 'reguisheim', 'france');
$repository->save($address);

$address->setStreet('lot. les erables');
$address->setStreet('lot. les Erables');
$repository->save($address);

$address->setPreviousOne(new Address(1, 'rue des champs', 'rexa', 'france'));
$repository->save($address);

var_dump($repository->find('Knp\Event\Example\Crud\Address', (string)$address->getId())->get());
