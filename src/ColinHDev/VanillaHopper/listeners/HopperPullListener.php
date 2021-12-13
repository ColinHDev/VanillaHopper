<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\events\HopperPullEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;

class HopperPullListener implements Listener {

    /**
     * When a hopper pulls an item, its inventory gets fuller. That's why it only needs to check the block below, which
     * would be the only hopper able to pull from it, and the block above, as this hopper just pulled from it and it may
     * be able to either push another item into this hopper or pull an item itself.
     * @var int[]
     */
    private const FACINGS = [
        Facing::DOWN,
        Facing::UP
    ];

    /**
     * The origin block's inventory got an item removed. Therefore all blocks around it, from where a hopper would be
     * able to push into the origin block, need to be checked, as they could now be able to deposit another item into
     * that block.
     * @var int[]
     */
    private const FACINGS_ORIGIN = [
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
    public function onHopperPull(HopperPullEvent $event) : void {
        $position = $event->getBlock()->getPosition();
        foreach (self::FACINGS as $facing) {
            $block = $position->world->getBlock($position->getSide($facing));
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
        $origin = $event->getOrigin()->getPosition();
        foreach (self::FACINGS_ORIGIN as $facing) {
            $block = $origin->world->getBlock($origin->getSide($facing));
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
    }
}