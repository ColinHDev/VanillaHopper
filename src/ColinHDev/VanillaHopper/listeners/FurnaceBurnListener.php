<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper;
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

    public function onFurnaceBurn(FurnaceBurnEvent $event) : void {
        $position = $event->getFurnace()->getPosition();
        foreach (self::FACINGS as $facing) {
            $vector3 = $position->getSide($facing);
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