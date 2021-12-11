<?php

namespace ColinHDev\VanillaHopper\entities;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper as TileHopper;
use pocketmine\entity\object\ItemEntity as PMMPItemEntity;

class ItemEntity extends PMMPItemEntity {

    protected function move(float $dx, float $dy, float $dz) : void {
        parent::move($dx, $dy, $dz);

        // Only if the world did not change, e.g. due to a teleport, we need to check how far the entity moved.
        if ($this->location->world === $this->lastLocation->world) {
            // Check if the entity moved across a block and if not, we already checked that block and the entity just
            // moved in the borders between that one.
            if ($this->location->getFloorX() === $this->lastLocation->getFloorX() && $this->location->getFloorY() === $this->lastLocation->getFloorY() && $this->location->getFloorZ() === $this->lastLocation->getFloorZ()) {
                return;
            }
        }

        $tile = $this->location->world->getTile($this->location);
        if (!$tile instanceof TileHopper) {
            $tile = $this->location->world->getTile($this->location->down());
            if (!$tile instanceof TileHopper) {
                return;
            }
        }
        $position = $tile->getPosition();

        if (!$tile->isScheduledForDelayedBlockUpdate()) {
            $tile->setTransferCooldown(
                BlockUpdateScheduler::getInstance()->scheduleDelayedBlockUpdate(
                    $position->world,
                    $position,
                    0
                )
            );
            $tile->setScheduledForDelayedBlockUpdate(true);
        }

        ItemEntityManager::getInstance()->addEntityToHopper($position, $this);
    }
}