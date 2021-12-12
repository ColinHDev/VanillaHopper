<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\entities\ItemEntity;
use pocketmine\entity\object\ItemEntity as PMMPItemEntity;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;

class EntityMotionListener implements Listener {

    /**
     * We want this listener to still be executed even if the event was cancelled so that we can reverse our hack.
     * @handleCancelled true
     * We want this listener to be executed as soon as possible so that our hack does not affect any plugins.
     * @priority LOWEST
     */
    public function onEntityMotion(EntityMotionEvent $event) : void {
        $entity = $event->getEntity();
        if ($entity instanceof PMMPItemEntity && !$entity instanceof ItemEntity) {
            $event->cancel();
            $property = new \ReflectionProperty($entity, "justCreated");
            $property->setAccessible(true);
            $property->setValue($entity,true);
            $entity->flagForDespawn();
            $newEntity = new ItemEntity($entity->getLocation(), $entity->getItem());
            $newEntity->setPickupDelay($entity->getPickupDelay());
            $newEntity->setMotion($event->getVector());
            $newEntity->spawnToAll();
        }
    }
}