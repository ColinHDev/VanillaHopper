<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\events\HopperPullEvent;
use pocketmine\event\Listener;

class HopperPullListener implements Listener {

    public function onHopperPull(HopperPullEvent $event) : void {
        $position = $event->getBlock()->getPosition();
        foreach ($position->sides() as $vector3) {
            $block = $position->world->getBlock($vector3);
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }

        $origin = $event->getOrigin()->getPosition();
        foreach (array_merge([-1 => $origin->asVector3()], $origin->sidesArray()) as $vector3) {
            $block = $position->world->getBlock($vector3);
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
    }
}