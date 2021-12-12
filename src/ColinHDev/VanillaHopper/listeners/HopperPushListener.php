<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\events\HopperPushEvent;
use pocketmine\event\Listener;

class HopperPushListener implements Listener {

    public function onHopperPush(HopperPushEvent $event) : void {
        $position = $event->getBlock()->getPosition();
        foreach ($position->sides() as $vector3) {
            $block = $position->world->getBlock($vector3);
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }

        $destination = $event->getDestination()->getPosition();
        foreach (array_merge([-1 => $destination->asVector3()], $destination->sidesArray()) as $vector3) {
            $block = $position->world->getBlock($vector3);
            if ($block instanceof Hopper) {
                $block->scheduleDelayedBlockUpdate(1);
            }
        }
    }
}