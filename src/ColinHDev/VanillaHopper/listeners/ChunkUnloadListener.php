<?php

namespace ColinHDev\VanillaHopper\listeners;

use ColinHDev\VanillaHopper\blocks\BlockDataStorer;
use pocketmine\event\Listener;
use pocketmine\event\world\ChunkUnloadEvent;

class ChunkUnloadListener implements Listener {

    /**
     * If the event was cancelled, we do not want to remove the chunk from {@link BlockDataStorer}, so no data is
     * accidentally lost.
     * @handleCancelled false
     * We want this listener to be executed as late as possible so that every plugin has the chance to cancel the event
     * so that no chunk is removed although the next listener may cancel the event.
     * @priority MONITOR
     */
    public function onChunkUnload(ChunkUnloadEvent $event) : void {
        BlockDataStorer::getInstance()->removeChunk($event->getWorld(), $event->getChunkX(), $event->getChunkZ());
    }
}