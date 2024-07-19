<?php
namespace App\EventListener;

use App\Entity\Post;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class EntityListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Post) {
            if ($entity->getCreatedAt() === null) {
                $entity->setCreatedAt(new \DateTimeImmutable()); // Définit la date de création
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Post) {
            $entity->setUpdatedAt(new \DateTimeImmutable()); // Définit la date de mise à jour
        }
    }
}
