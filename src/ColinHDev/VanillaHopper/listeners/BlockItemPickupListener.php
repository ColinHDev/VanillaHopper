<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\event\block\BlockItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;

class BlockItemPickupListener implements Listener {

    /**
     * The BlockItemPickupEvent is called when a block tries to pick up an item entity. In this case, only the block
     * below needs to be checked, as if the original block is hopper, it is currently inside a block update, and only
     * the block below needs to be checked, as it would be the only hopper that would be able to pull from the original
     * block. All other potential hoppers around could only push into the block and therefore do not need to be updated
     * if the original block's inventory would get fuller.
     * @var int[]
     */
    private const FACINGS = [
        Facing::DOWN
    ];

    /**
     * If the event was cancelled, we don't need to schedule a delayed block update, because nothing changed.
     * @handleCancelled false
     * We want this listener to be executed as late as possible so that every plugin has the chance to cancel the event
     * so that no block update will be scheduled although the next listener may cancel the event.
     * @priority MONITOR
     */
    public function onBlockItemPickup(BlockItemPickupEvent $event) : void {
        $position = $event->getBlock()->getPosition();
        $world = $position->getWorld();
        foreach (self::FACINGS as $facing) {
            $block = $world->getBlock($position->getSide($facing));
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
    }
}