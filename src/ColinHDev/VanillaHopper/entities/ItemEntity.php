<?php

namespace ColinHDev\VanillaHopper\entities;

use ColinHDev\VanillaHopper\blocks\BlockDataStorer;
use ColinHDev\VanillaHopper\blocks\Hopper;
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
        $this->checkForHopper();
    }

    /**
     * Checks if this item entity is on or inside a hopper and if so, assigns the entity to the hopper.
     * @return bool Returns TRUE if a hopper was found and the item entity was assigned to the hopper, FALSE otherwise.
     */
    private function checkForHopper() : bool {
        // Checking the block at the item entity's position.
        $block = $this->location->world->getBlock($this->location);
        if (!$block instanceof Hopper) {
            // Checking the block below the item entity.
            $block = $this->location->world->getBlock($this->location->down());
            if (!$block instanceof Hopper) {
                return false;
            }
        }
        $block->scheduleDelayedBlockUpdate(0);
        BlockDataStorer::getInstance()->assignEntity($block->getPosition(), $this);
        return true;
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