<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper;
use ColinHDev\VanillaHopper\events\HopperPushEvent;
use pocketmine\event\Listener;

class HopperPushListener implements Listener {

    public function onHopperPush(HopperPushEvent $event) : void {
        $position = $event->getBlock()->getPosition();
        foreach ($position->sides() as $vector3) {
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

        $destination = $event->getDestination()->getPosition();
        foreach (array_merge([-1 => $destination->asVector3()], $destination->sidesArray()) as $vector3) {
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