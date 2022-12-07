<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\entities\ItemEntity;
use pocketmine\entity\object\ItemEntity as PMMPItemEntity;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;

class EntitySpawnListener implements Listener {

    /**
     * We want this listener to be executed as soon as possible so that this entity is flagged for despawn immediately.
     * @priority LOWEST
     */
    public function onEntitySpawn(EntitySpawnEvent $event) : void {
        $entity = $event->getEntity();
        // We obviously only want to despawn this entity and spawn our replacement, if the entity was spawned by
        // PocketMine-MP and not us.
        if ($entity instanceof PMMPItemEntity && !$entity instanceof ItemEntity) {
            // Flagging the old entity for despawn and spawning our custom one with the old's properties.
            $entity->flagForDespawn();
            $newEntity = new ItemEntity($entity->getLocation(), $entity->getItem());
            $newEntity->setPickupDelay($entity->getPickupDelay());
            $newEntity->setMotion($entity->getMotion());
            $newEntity->spawnToAll();
        }
    }
}