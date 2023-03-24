<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\events\HopperPushEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;

class HopperPushListener implements Listener {

    /**
     * When a hopper pushes an item, its inventory gets emptier. Therefore it checks the blocks horizontally and above
     * it, as those hoppers would be able to push an item into this hopper, as it could have some space again.
     * @var int[]
     */
    private const FACINGS = [
        Facing::UP,
        Facing::NORTH,
        Facing::SOUTH,
        Facing::WEST,
        Facing::EAST
    ];

    /**
     * The destination block's inventory gets fuller. Therefore it only needs to check the block below it, as it would
     * be the only hopper that could pull from the destination block.
     * @var int[]
     */
    private const FACINGS_DESTINATION = [
        Facing::DOWN
    ];

    /**
     * If the event was cancelled, we don't need to schedule a delayed block update, because nothing changed.
     * @handleCancelled false
     * We want this listener to be executed as late as possible so that every plugin has the chance to cancel the event
     * so that no block update will be scheduled although the next listener may cancel the event.
     * @priority MONITOR
     */
    public function onHopperPush(HopperPushEvent $event) : void {
        $position = $event->getBlock()->getPosition();
        $world = $position->getWorld();
        foreach (self::FACINGS as $facing) {
            $block = $world->getBlock($position->getSide($facing));
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
        $destination = $event->getDestination()->getPosition();
        $destinationWorld = $destination->getWorld();
        foreach (self::FACINGS_DESTINATION as $facing) {
            $block = $destinationWorld->getBlock($destination->getSide($facing));
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
    }
}