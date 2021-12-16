<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class Subscriber implements EventSubscriber
{
    public function __construct(private SerializationHandler $handler)
    {
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->handler->serialize($args->getEntity());
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->handler->serialize($args->getEntity());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->handler->deserialize($args->getEntity());
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->handler->deserialize($args->getEntity());
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $this->handler->deserialize($args->getEntity());
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::prePersist,
            Events::postUpdate,
            Events::postPersist,
            Events::postLoad,
        ];
    }
}
