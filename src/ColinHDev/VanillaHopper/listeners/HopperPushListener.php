<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\events\HopperPushEvent;
use pocketmine\event\Listener;

class HopperPushListener implements Listener {

    /**
     * If the event was cancelled, we don't need to schedule a delayed block update, because nothing changed.
     * @handleCancelled false
     * We want this listener to be executed as late as possible so that every plugin has the chance to cancel the event
     * so that no block update will be scheduled although the next listener may cancel the event.
     * @priority MONITOR
     */
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