<?php

namespace funk\Knp\Event\Concurrency;

use example\Shop\Model\Product;
use Knp\Event\Store;
use Knp\Event\Exception\Concurrency\Optimistic\Conflict;
use Knp\Event\Repository;
use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;

class Optimistic implements \Funk\Spec
{
    private $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    function it_allows_to_store_aggregates_at_same_verison()
    {
        $repository = (new Repository\Factory(new Store\Concurrency\Optimistic($this->store, new VersionTransporter\Http(1))))->create();

        $repository->save($product = (new Product(null, 'test')));
        $fetch = $repository->find(Product::class, (string)$product->getId())->get();
        expect($repository)->toNotThrow(Conflict::class)->during('save', [$fetch]);
    }

    function it_refuses_to_store_superseeded_versions()
    {
        $directRepo = (new Repository\Factory($this->store))->create();
        $repository = (new Repository\Factory(new Store\Concurrency\Optimistic($this->store, new VersionTransporter\Http(1))))->create();

        $product = new Product(null, 'test');
        $product->rename('1');
        $product->rename('2');
        $directRepo->save($product);

        $fetch = $repository->find(Product::class, (string)$product->getId())->get();
        $fetch->rename('change 1');
        expect($repository)->toThrow(Conflict::class)->during('save', [$fetch]);
    }
}
