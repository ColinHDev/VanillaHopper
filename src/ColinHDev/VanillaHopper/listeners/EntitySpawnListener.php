<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\entities\ItemEntity;
use pocketmine\entity\object\ItemEntity as PMMPItemEntity;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;

class EntitySpawnListener implements Listener {

    /**
     * We want this listener to be executed as late as possible so that our hack does not affect any plugins.
     * @priority MONITOR
     */
    public function onEntitySpawn(EntitySpawnEvent $event) : void {
        $entity = $event->getEntity();
        if ($entity instanceof PMMPItemEntity && !$entity instanceof ItemEntity) {
            $property = new \ReflectionProperty($entity, "justCreated");
            $property->setAccessible(true);
            $property->setValue($entity,false);
        }
    }
}