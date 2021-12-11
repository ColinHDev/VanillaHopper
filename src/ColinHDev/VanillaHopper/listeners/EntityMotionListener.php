<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\entities\ItemEntity;
use pocketmine\entity\object\ItemEntity as PMMPItemEntity;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;

class EntityMotionListener implements Listener {

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