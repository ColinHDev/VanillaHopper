<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\entities\ItemEntityManager;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\Listener;

class EntityDespawnListener implements Listener {

    public function onEntityDespawn(EntityDespawnEvent $event) : void {
        $entity = $event->getEntity();
        if ($entity instanceof ItemEntity) {
            ItemEntityManager::getInstance()->removeItemEntity($entity);
        }
    }
}