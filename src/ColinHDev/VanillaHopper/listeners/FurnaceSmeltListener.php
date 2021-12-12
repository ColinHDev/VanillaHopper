<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;

class FurnaceSmeltListener implements Listener {

    /**
     * The FurnaceSmeltEvent is called when the furnace smelted an input item to a result item. If that is the case, the
     * only hoppers that could be affected is the hopper above, which could now be able to push another input item into
     * the furnace, and the hopper below, which could now be able to pull the result item out of the furnace. That's why
     * we don't need the check the blocks horizontally placed to the furnace, as those hoppers could only push to the
     * furnace's fuel slot.
     * @var int[]
     */
    private const FACINGS = [
        Facing::DOWN,
        Facing::UP
    ];

    public function onFurnaceSmelt(FurnaceSmeltEvent $event) : void {
        $position = $event->getFurnace()->getPosition();
        foreach (self::FACINGS as $facing) {
            $block = $position->world->getBlock($position->getSide($facing));
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
    }
}