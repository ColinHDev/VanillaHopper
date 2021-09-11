<?php

namespace ColinHDev\VanillaHopper\blocks;

use ColinHDev\VanillaHopper\blocks\tiles\Hopper as TileHopper;
use ColinHDev\VanillaHopper\events\HopperPullContainerEvent;
use ColinHDev\VanillaHopper\events\HopperPushContainerEvent;
use ColinHDev\VanillaHopper\events\HopperPushJukeboxEvent;
use ColinHDev\VanillaHopper\ResourceManager;
use pocketmine\block\Block;
use pocketmine\block\Hopper as PMMP_Hopper;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\Jukebox;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace as TileFurnace;
use pocketmine\block\tile\Jukebox as TileJukebox;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockItemPickupEvent;
use pocketmine\item\Bucket;
use pocketmine\item\Record;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class Hopper extends PMMP_Hopper {

    public function onScheduledUpdate() : void {
        $tile = $this->position->getWorld()->getTile($this->position);
        if (!$tile instanceof TileHopper) {
            // We don't schedule another update because we can't work with a hopper that has no tile and therefore no inventory.
            // The update should be scheduled on its own by the tile if it is eventually created.
            return;
        }

        $transferCooldown = $tile->getTransferCooldown();
        $currentTick = $this->position->getWorld()->getServer()->getTick();
        if ($transferCooldown > 0) {
            $transferCooldown -= ($currentTick - ($tile->getLastTick() ?? ($currentTick - 1)));
        }

        if (!$this->isPowered() && $transferCooldown <= 0) {
            $inventory = $tile->getInventory();
            $success = $this->push($inventory);
            // Hoppers that have a block above them from which they can pull from, won't try to pick up items.
            // TODO: Hoppers not only can pull from blocks, but from entities too (for example: Minecarts).
            $origin = $this->getPullable();
            if ($origin !== null) {
                $success = $this->pull($inventory, $origin) || $success;
            } else {
                $success = $this->pickup($inventory) || $success;
            }
            // The cooldown is only set back to the default amount of ticks if the hopper has done anything.
            if ($success || ResourceManager::getInstance()->getAlwaysSetCooldown()) {
                $transferCooldown = ResourceManager::getInstance()->getDefaultTransferCooldown();
            }
        }
        $tile->setTransferCooldown(max(0, $transferCooldown));
        $tile->setLastTick($currentTick);
    }

    /**
     * This function handles pushing items from the hopper to a tile in the direction the hopper is facing.
     * Returns true if an item was successfully pushed or false on failure.
     */
    private function push(HopperInventory $inventory) : bool{
        if(count($inventory->getContents()) === 0){
            return false;
        }
        // TODO: Hoppers not only can push to blocks, but to entities too (for example: Minecarts).
        $destination = $this->position->getWorld()->getTile($this->position->getSide($this->getFacing()));
        if($destination === null){
            return false;
        }

        $itemsToTransfer = ResourceManager::getInstance()->getItemsPerUpdate();
        for($slot = 0; $slot < $inventory->getSize(); $slot++){
            if ($itemsToTransfer <= 0) {
                return true;
            }

            $item = $inventory->getItem($slot);
            if($item->isNull()){
                continue;
            }
            if ($item->getCount() >= $itemsToTransfer) {
                $itemToPush = $item->pop($itemsToTransfer);
            } else {
                $itemToPush = $item->pop($item->getCount());
            }

            // Hoppers interact differently when pushing into different kinds of tiles.
            //TODO: Composter
            //TODO: Brewing Stand
            //TODO: Jukebox (improve)
            if($destination instanceof TileFurnace){
                // If the hopper is facing down, it will push every item to the furnace's input slot, even items that aren't smeltable.
                // If the hopper is facing in any other direction, it will only push items that can be used as fuel to the furnace's fuel slot.
                if($this->getFacing() === Facing::DOWN){
                    $slotInFurnace = FurnaceInventory::SLOT_INPUT;
                    $itemInFurnace = $destination->getInventory()->getSmelting();
                }else{
                    if($item->getFuelTime() === 0){
                        continue;
                    }
                    $slotInFurnace = FurnaceInventory::SLOT_FUEL;
                    $itemInFurnace = $destination->getInventory()->getFuel();
                }
                if(!$itemInFurnace->isNull()){
                    if($itemInFurnace->getCount() >= $itemInFurnace->getMaxStackSize()){
                        return false;
                    }
                    if(!$itemInFurnace->canStackWith($item)){
                        continue;
                    }
                    $itemInFurnace->setCount($itemInFurnace->getCount() + $itemToPush->getCount());
                } else {
                    $itemInFurnace = $itemToPush;
                }

                $event = new HopperPushContainerEvent($this, $inventory, $destination->getBlock(), $destination->getInventory(), $itemToPush);
                $event->call();
                if ($event->isCancelled()) {
                    continue;
                }

                $itemsToTransfer -= $itemToPush->getCount();
                $inventory->removeItem($itemToPush);
                $destination->getInventory()->setItem($slotInFurnace, $itemInFurnace);
                continue;

            }elseif($destination instanceof TileHopper){
                if(!$destination->getInventory()->canAddItem($itemToPush)){
                    continue;
                }
                // Hoppers pushing into empty hoppers set the empty hoppers transfer cooldown back to the default amount of ticks.
                if(count($destination->getInventory()->getContents()) === 0){
                    $destination->setTransferCooldown(ResourceManager::getInstance()->getDefaultTransferCooldown());
                }

            }elseif($destination instanceof TileJukebox){
                if(!($itemToPush instanceof Record)){
                    continue;
                }
                //TODO:
                // Jukeboxes actually emit a redstone signal when playing a record so nearby hoppers are blocked and
                // prevented from inserting another disk. Because neither does redstone work properly nor can we check if
                // a jukebox is still playing a record or has already finished it, we can just check if it has already a
                // record inserted.
                if($destination->getRecord() !== null){
                    return false;
                }

                // The Jukebox block is handling the playing of records, so we need to get it here and can't use TileJukebox::setRecord().
                $jukebox = $destination->getBlock();
                if($jukebox instanceof Jukebox){
                    $event = new HopperPushJukeboxEvent($this, $inventory, $jukebox, $itemToPush);
                    $event->call();
                    if ($event->isCancelled()) {
                        continue;
                    }

                    $jukebox->insertRecord($itemToPush);
                    $jukebox->getPosition()->getWorld()->setBlock($jukebox->getPosition(), $jukebox);
                    $inventory->removeItem($itemToPush);
                    return true;
                }
                return false;

            }elseif($destination instanceof Container){
                if(!$destination->getInventory()->canAddItem($itemToPush)){
                    continue;
                }

            }else{
                return false;
            }

            $event = new HopperPushContainerEvent($this, $inventory, $destination->getBlock(), $destination->getInventory(), $itemToPush);
            $event->call();
            if ($event->isCancelled()) {
                continue;
            }

            $itemsToTransfer -= $itemToPush->getCount();
            $inventory->removeItem($itemToPush);
            $destination->getInventory()->addItem($itemToPush);
        }
        if ($itemsToTransfer !== 0 && $itemsToTransfer === ResourceManager::getInstance()->getItemsPerUpdate()) {
            return false;
        }
        return true;
    }

    /**
     * This function checks if the hopper has a block above it, from which he can pull.
     * Returns the found block or null if there is no block above or he can't pull from it.
     * TODO: Hoppers not only can pull from blocks, but from entities too (for example: Minecarts).
     */
    private function getPullable() : ?Block {
        // Hoppers can pull from all kinds of containers.
        // So, if we have a container given, we don't need to compare the block itself with all possible block types.
        $tile = $this->position->getWorld()->getTile($this->position->getSide(Facing::UP));
        if ($tile instanceof Container) {
            return $tile->getBlock();
        }

        // We don't need to check if the block is a chest, furnace, etc. because the corresponding tile of that blocks is a container.
        // Of course, these blocks couldn't have a tile set, so the upper if statement couldn't return,
        // but then they are useless anyway because, without a tile, they don't have an inventory to pull from.
        $block = $this->position->getWorld()->getBlock($this->position->getSide(Facing::UP));
        return match (true) {
            $block instanceof Jukebox => $block,
            default => null
        };
    }

    /**
     * This function handles pulling items by the hopper from a block above.
     * Returns true if an item was successfully pulled or false on failure.
     */
    private function pull(HopperInventory $inventory, Block $origin) : bool{
        $itemsToTransfer = ResourceManager::getInstance()->getItemsPerUpdate();
        // Hoppers interact differently when pulling from different kinds of blocks.
        //TODO: Composter
        //TODO: Brewing Stand
        //TODO: Jukebox
        $originTile = $origin->position->getWorld()->getTile($origin->position);
        if ($originTile instanceof Container) {
            if ($originTile instanceof TileFurnace) {
                foreach ([FurnaceInventory::SLOT_FUEL, FurnaceInventory::SLOT_RESULT] as $slot) {
                    // Hoppers either pull empty buckets from the furnace's fuel slot or pull from its result slot.
                    // They prioritise pulling from the fuel slot over the result slot.
                    $item = $originTile->getInventory()->getItem($slot);
                    if ($slot === FurnaceInventory::SLOT_FUEL && !$item instanceof Bucket) {
                        continue;
                    }
                    if ($item->isNull()) {
                        continue;
                    }
                    if ($item->getCount() >= $itemsToTransfer) {
                        $itemToPull = $item->pop($itemsToTransfer);
                    } else {
                        $itemToPull = $item->pop($item->getCount());
                    }

                    $event = new HopperPullContainerEvent($this, $inventory, $origin, $originTile->getInventory(), $itemToPull);
                    $event->call();
                    if ($event->isCancelled()) {
                        continue;
                    }

                    $itemsToTransfer -= $itemToPull->getCount();
                    $inventory->addItem($itemToPull);
                    $originTile->getInventory()->setItem($slot, $item);
                }
            } else {
                for($slot = 0; $slot < $originTile->getInventory()->getSize(); $slot++){
                    $item = $originTile->getInventory()->getItem($slot);
                    if($item->isNull()){
                        continue;
                    }
                    if ($item->getCount() >= $itemsToTransfer) {
                        $itemToPull = $item->pop($itemsToTransfer);
                    } else {
                        $itemToPull = $item->pop($item->getCount());
                    }
                    if(!$inventory->canAddItem($itemToPull)){
                        continue;
                    }

                    $event = new HopperPullContainerEvent($this, $inventory, $origin, $originTile->getInventory(), $itemToPull);
                    $event->call();
                    if ($event->isCancelled()) {
                        continue;
                    }

                    $itemsToTransfer -= $itemToPull->getCount();
                    $inventory->addItem($itemToPull);
                    $originTile->getInventory()->removeItem($itemToPull);
                }
            }
        }
        if ($itemsToTransfer !== 0 && $itemsToTransfer === ResourceManager::getInstance()->getItemsPerUpdate()) {
            return false;
        }
        return true;
    }

    /**
     * This function handles picking up items by the hopper.
     * Returns true if an item was successfully picked up or false on failure.
     */
    private function pickup(HopperInventory $inventory) : bool {
        $itemsToTransfer = ResourceManager::getInstance()->getItemsPerUpdate();
        // In Bedrock Edition hoppers collect from the lower 3/4 of the block space above them.
        $pickupCollisionBox = new AxisAlignedBB(
            $this->position->getX(),
            $this->position->getY() + 1,
            $this->position->getZ(),
            $this->position->getX() + 1,
            $this->position->getY() + 1.75,
            $this->position->getZ() + 1
        );
        foreach($this->position->getWorld()->getNearbyEntities($pickupCollisionBox) as $entity){
            if ($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity instanceof ItemEntity) {
                continue;
            }
            if ($itemsToTransfer <= 0) {
                return true;
            }
            // Unlike Java Edition, Bedrock Edition's hoppers don't save in which order item entities landed on top of them to collect them in that order.
            // In Bedrock Edition hoppers collect item entities in the order in which they entered the chunk.
            // Because of how entities are saved by PocketMine-MP the first entities of this loop are also the first ones who were saved.
            // That's why we don't need to implement any sorting mechanism.
            $item = $entity->getItem();
            if(!$inventory->canAddItem($item)){
                continue;
            }

            $event = new BlockItemPickupEvent($this, $entity, $item, $inventory);
            $event->call();
            if ($event->isCancelled()) {
                continue;
            }

            $itemsToTransfer--;
            $event->getInventory()->addItem($event->getItem());
            $event->getOrigin()->flagForDespawn();
        }
        if ($itemsToTransfer !== 0 && $itemsToTransfer === ResourceManager::getInstance()->getItemsPerUpdate()) {
            return false;
        }
        return true;
    }
}