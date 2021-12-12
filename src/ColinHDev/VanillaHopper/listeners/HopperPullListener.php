<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper;
use ColinHDev\VanillaHopper\events\HopperPullEvent;
use pocketmine\event\Listener;

class HopperPullListener implements Listener {

    public function onHopperPull(HopperPullEvent $event) : void {
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

        $origin = $event->getOrigin()->getPosition();
        foreach (array_merge([-1 => $origin->asVector3()], $origin->sidesArray()) as $vector3) {
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