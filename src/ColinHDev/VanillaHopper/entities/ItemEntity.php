<?php

namespace ColinHDev\VanillaHopper\entities;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper as TileHopper;
use pocketmine\entity\object\ItemEntity as PMMPItemEntity;
use pocketmine\item\Item;

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

        $tile->addAssignedEntity($this);
    }

    /**
     * Set the item of the item entity.
     * We need this method to change the item entity's item when part of it was picked up by a hopper. We can't just
     * spawn a new entity then, as that new entity would have "entered" the chunk after all other entities. But during
     * testing in vanilla, it was shown, that those partly picked up entities are still favoured by hoppers when picking
     * up items than other entities.
     * @link Hopper::pickup()
     */
    public function setItem(Item $item) : void {
        $this->item = clone $item;
        // Hack: When the item changes, we want all clients to see that, as the item could be a completely different one
        // because BlockItemPickupEvent's setItem() method provides the ability to completely change the item.
        // That's why we despawn and then respawn the entity to all of its viewers again.
        $this->despawnFromAll();
        $this->spawnToAll();
    }
}