<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;

class FurnaceBurnListener implements Listener {

    /**
     * As hoppers can only affect the fuel slot of a furnace by either pushing into it
     * (when the hopper is facing horizontally) or by pulling the item from the fuel slot (e.g. an empty bucket)
     * (when the hopper is placed below), we don't need to schedule a delayed block update for the block above since
     * that can only push into the furnace's input slot.
     * @var int[]
     */
    private const FACINGS = [
        Facing::DOWN,
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
    public function onFurnaceBurn(FurnaceBurnEvent $event) : void {
        $position = $event->getFurnace()->getPosition();
        $world = $position->getWorld();
        foreach (self::FACINGS as $facing) {
            $block = $world->getBlock($position->getSide($facing));
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
    }
}