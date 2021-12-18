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
        // This hack should only affect PocketMine-MP's item entities and not our own ones.
        if ($entity instanceof PMMPItemEntity && !$entity instanceof ItemEntity) {
            // This is a hack. PocketMine-MP currently does not support overwriting existing entities, so a workaround
            // had to be found.
            // Item entities are spawned with the World::dropItem() method. We can't spawn our custom entity here, as
            // this event is called in the entities constructor. But properties like the pickup delay and the motion are
            // not set yet, so we can not spawn our entity, as we do not have that information.
            // The order in which item entities are spawned is: constructing the entity, setting its pickup delay,
            // setting its motion, spawning it to all viewers. We need to set the value of the "justCreated" property so
            // that the EntityMotionEvent is called when the motion is set.
            $property = new \ReflectionProperty($entity, "justCreated");
            $property->setAccessible(true);
            $property->setValue($entity,false);
        }
    }
}