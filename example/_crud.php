<?php

use example\Shop\Model\Product;
use Knp\Event\Repository;
use Knp\Event\Store;

require __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();

$dispatcher = new \Knp\Event\Dispatcher;
$conn = \Doctrine\DBAL\DriverManager::getConnection([
        'dbname' => 'event_store_projection',
        'user' => 'florian',
        'password' => null,
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
    ]
);

$dispatcher->add(new \Knp\Event\Crud\Projection($conn));
$store = new Store\Logger(new Store\InMemory);
$repository = (new Repository\Factory($store, $dispatcher))->create();

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

var_dump($repository->find('Address', (string)$address->getId())->get());
