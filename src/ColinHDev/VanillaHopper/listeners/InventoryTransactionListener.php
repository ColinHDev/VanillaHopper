<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

class InventoryTransactionListener implements Listener {

    /**
     * As InventoryTransactions are highly complicated and are difficult to evaluate data from, we want to check the
     * inventory holder block and every block around it, if it is a hopper and schedule a delayed block update if so,
     * so that there should not be any falsely un-updated hoppers.
     * Therefore we use all facing values, as well as -1 which reduces code length and duplication as it just lets
     * @link Vector3::getSide() return itself, as -1 is not the value of a valid facing.
     * @var int[]
     */
    private const FACINGS = [
        -1,
        Facing::DOWN,
        Facing::UP,
        Facing::NORTH,
        Facing::SOUTH,
        Facing::WEST,
        Facing::EAST
    ];

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
                foreach (self::FACINGS as $facing) {
                    $block = $position->world->getBlock($position->getSide($facing));
                    if ($block instanceof Hopper) {
                        $block->scheduleDelayedBlockUpdate(1);
                    }
                }
            }
        }
    }
}