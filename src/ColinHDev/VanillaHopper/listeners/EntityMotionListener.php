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
        // This hack should only affect PocketMine-MP's item entities and not our own ones.
        if ($entity instanceof PMMPItemEntity && !$entity instanceof ItemEntity) {
            // This is a hack. PocketMine-MP currently does not support overwriting existing entities, so a workaround
            // had to be found.
            // As we succeeded with our hack, we can set the value of the "justCreated" property back to false, so that
            // this plugin does not lead to any weird behaviour.
            $property = new \ReflectionProperty($entity, "justCreated");
            $property->setAccessible(true);
            $property->setValue($entity,true);
            // We don't need the original entity anymore, as we now spawn our custom one.
            $entity->flagForDespawn();
            // Unlike in the EntitySpawnEvent, we now have all the necessary information needed to spawn our custom
            // item entity.
            $newEntity = new ItemEntity($entity->getLocation(), $entity->getItem());
            $newEntity->setPickupDelay($entity->getPickupDelay());
            $newEntity->setMotion($event->getVector());
            $newEntity->spawnToAll();
            // We cancel this event so that other plugins do not handle it. This event should even be called normally
            // for this entity, as it was just created, so there is no point in other plugins handling it.
            $event->cancel();
        }
    }
}