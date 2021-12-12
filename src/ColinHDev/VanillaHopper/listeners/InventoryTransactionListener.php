<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;

class InventoryTransactionListener implements Listener {

    /**
     * If the event was cancelled, we don't need to schedule a delayed block update, because nothing changed.
     * @handleCancelled false
     * We want this listener to be executed as late as possible so that every plugin has the chance to cancel the event
     * so that no block update will be scheduled although the next listener may cancel the event.
     * @priority MONITOR
     */
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