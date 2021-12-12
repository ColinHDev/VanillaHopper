<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;

class InventoryTransactionListener implements Listener {

    public function onInventoryTransaction(InventoryTransactionEvent $event) : void {
        foreach ($event->getTransaction()->getInventories() as $inventory) {
            if ($inventory instanceof BlockInventory) {
                $position = $inventory->getHolder();
                foreach (array_merge([-1 => $position->asVector3()], $position->sidesArray()) as $vector3) {
                    $tile = $position->world->getTile($vector3);
                    if (!$tile instanceof Hopper) {
                        continue;
                    }
                    if (!$tile->isScheduledForDelayedBlockUpdate()) {
                        $tile->setTransferCooldown(
                            BlockUpdateScheduler::getInstance()->scheduleDelayedBlockUpdate($position->world, $vector3, 1)
                        );
                        $tile->setScheduledForDelayedBlockUpdate(true);
                    }
                }
            }
        }
    }
}