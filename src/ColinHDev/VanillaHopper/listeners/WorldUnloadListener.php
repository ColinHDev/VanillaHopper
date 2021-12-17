<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\BlockDataStorer;
use pocketmine\event\Listener;
use pocketmine\event\world\WorldUnloadEvent;

class WorldUnloadListener implements Listener {

    /**
     * If the event was cancelled, we do not want to remove the world from {@link BlockDataStorer}, so no data is
     * accidentally lost.
     * @handleCancelled false
     * We want this listener to be executed as late as possible so that every plugin has the chance to cancel the event
     * so that no world is removed although the next listener may cancel the event.
     * @priority MONITOR
     */
    public function onWorldUnload(WorldUnloadEvent $event) : void {
        BlockDataStorer::getInstance()->removeWorld($event->getWorld());
    }
}