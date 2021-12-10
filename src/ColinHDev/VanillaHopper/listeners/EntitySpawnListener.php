<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\entities\ItemEntityManager;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;

class EntitySpawnListener implements Listener {

    public function onEntitySpawn(EntitySpawnEvent $event) : void {
        $entity = $event->getEntity();
        if ($entity instanceof ItemEntity) {
            ItemEntityManager::getInstance()->addItemEntity($entity);
        }
    }
}