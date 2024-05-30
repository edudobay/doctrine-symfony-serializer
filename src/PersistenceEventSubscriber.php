<?php

declare(strict_types=1);

namespace Edudobay\DoctrineSerializable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class PersistenceEventSubscriber implements EventSubscriber
{
    public function __construct(private SerializationHandler $handler)
    {
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->handler->serialize($args->getObject());
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->handler->serialize($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->handler->deserialize($args->getObject());
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->handler->deserialize($args->getObject());
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $this->handler->deserialize($args->getObject());
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
