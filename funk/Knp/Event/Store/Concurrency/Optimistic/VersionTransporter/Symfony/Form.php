<?php

namespace funk\Knp\Event\Store\Concurrency\Optimistic\VersionTransporter\Symfony;

use example\Shop\Model\Product;
use Symfony\Component\Form\Forms;
use Knp\Event\Store;
use Knp\Event\Repository;
use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;
use Rhumsaa\Uuid\Uuid;
use Knp\Event\Exception\Concurrency\Optimistic\Conflict;

class Form implements \Funk\Spec
{
    private $repository;
    private $forms;

    public function __construct(Store $store)
    {
        $this->store = $store;

        $this->forms = Forms::createFormFactoryBuilder()->addTypeExtension($ext = new VersionTransporter\Symfony\Form)->getFormFactory();
        $this->repository = (new Repository\Factory(new Store\Concurrency\Optimistic($this->store, $ext)))->create();
    }

    function it_adds_a_default_version_if_not_yet_saved()
    {
        $fetch = (new Product(null, 'test'));

        $view = $this->forms->create('form', $fetch)->createView();
        expect($view)->toHaveKey('version');
        expect($view['version']->vars['value'])->toEqual('1');
    }

    function it_adds_current_version_as_hidden()
    {
        $product = new Product($id = Uuid::uuid4());
        $product->rename('A');
        $product->rename('B');
        $this->repository->save($product);
        $fetch = $this->repository->find(Product::class, (string)$id)->get();

        $view = $this->forms->create('form', $fetch)->createView();
        expect($view)->toHaveKey('version');
        expect($view['version']->vars['value'])->toEqual('3');
    }

    function it_compares_submitted_version_and_refuses_conflict()
    {
        $product = new Product($id = Uuid::uuid4());
        $product->rename('A');
        $this->repository->save($product);
        $fetch1 = $this->repository->find(Product::class, (string)$id)->get();
        $form = $this->forms->create('form', $fetch1);

        $form->submit(['version' => 3]);
        expect($this->repository)->toThrow(Conflict::class)->during('save', [$fetch1]);
    }
}
