<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;

class InventoryTransactionListener implements Listener {

    public function onInventoryTransaction(InventoryTransactionEvent $event) : void {
        foreach ($event->getTransaction()->getInventories() as $inventory) {
            if ($inventory instanceof BlockInventory) {
                $position = $inventory->getHolder();
                foreach (array_merge([-1 => $position->asVector3()], $position->sidesArray()) as $vector3) {
                    $block = $position->world->getBlock($vector3);
                    if ($block instanceof Hopper) {
                        $block->scheduleDelayedBlockUpdate(1);
                    }
                }
            }
        }
    }
}