<?php

namespace Knp\Event\Store\Concurrency\Optimistic\VersionTransporter\Symfony;

use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;
use Knp\Event\Emitter\HasIdentity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class Form extends AbstractTypeExtension implements VersionTransporter, EventSubscriberInterface
{
    private $key;
    private $default;
    private $versions = [];
    private $submittedVersions = [];

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function __construct($key = 'version', $default = 1)
    {
        $this->key = $key;
        $this->default = $default;
    }

    public function getExtendedType()
    {
        return 'form';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this);
    }

    public function getExpectedVersion($class, $id)
    {
        if (empty($this->submittedVersions[$class][$id])) {
            return $this->default;
        }

        return $this->submittedVersions[$class][$id];
    }

    public function update($class, $id, $version)
    {
        $this->versions[$class][$id] = $version;
    }

    public function onPostSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (!$data instanceof HasIdentity) {
            return;
        }

        $version = 1;
        if (isset($this->versions[get_class($data)][(string)$data->getId()])) {
            $version = $this->versions[get_class($data)][(string)$data->getId()];
        }

        $form->add($this->key, 'hidden', [
            'data' => $version,
            'mapped' => false,
        ]);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $emitter = $form->getData();

        if (empty($data[$this->key])) {
            return;
        }

        if (!$emitter instanceof HasIdentity) {
            return;
        }

        $this->submittedVersions[get_class($emitter)][(string)$emitter->getId()] = $data[$this->key];
    }
}
