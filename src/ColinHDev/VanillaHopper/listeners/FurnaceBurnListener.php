<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\Listener;

class FurnaceBurnListener implements Listener {

    public function onFurnaceBurn(FurnaceBurnEvent $event) : void {
        $position = $event->getFurnace()->getPosition();
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
    }
}