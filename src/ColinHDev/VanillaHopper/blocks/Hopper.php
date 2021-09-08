<?php

namespace ColinHDev\VanillaHopper\blocks;

use ColinHDev\VanillaHopper\blocks\tiles\Hopper as TileHopper;
use ColinHDev\VanillaHopper\events\HopperTransferContainerEvent;
use ColinHDev\VanillaHopper\events\HopperTransferJukeboxEvent;
use pocketmine\block\Hopper as PMMP_Hopper;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\Jukebox;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace as TileFurnace;
use pocketmine\block\tile\Jukebox as TileJukebox;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockItemPickupEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Bucket;
use pocketmine\item\Record;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class Hopper extends PMMP_Hopper {

    public function onScheduledUpdate() : void{
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);

        $tile = $this->position->getWorld()->getTile($this->position);
        if(!$tile instanceof TileHopper){
            return;
        }

        $transferCooldown = $tile->getTransferCooldown();
        if($transferCooldown > 0){
            $transferCooldown--;
            $tile->setTransferCooldown($transferCooldown);
        }

        if($this->isPowered() || $transferCooldown > 0){
            return;
        }

        $inventory = $tile->getInventory();
        $success = $this->push($inventory);
        // Hoppers that have a container above them, won't try to pick up items.
        $origin = $this->position->getWorld()->getTile($this->position->getSide(Facing::UP));
        //TODO: Not all blocks a hopper can pull from have an inventory (for example: Jukebox).
        if($origin instanceof Container){
            $success = $this->pull($inventory, $origin->getInventory()) || $success;
        }else{
            $success = $this->pickup($inventory) || $success;
        }
        // The cooldown is only set back to the default amount of ticks if the hopper has done anything.
        if($success){
            $tile->setTransferCooldown(TileHopper::DEFAULT_TRANSFER_COOLDOWN);
        }
    }

    /**
     * This function handles pushing items from the hopper to a tile in the direction the hopper is facing.
     * Returns true if an item was successfully pushed or false on failure.
     */
    private function push(HopperInventory $inventory) : bool{
        if(count($inventory->getContents()) === 0){
            return false;
        }
        $destination = $this->position->getWorld()->getTile($this->position->getSide($this->getFacing()));
        if($destination === null){
            return false;
        }

        for($slot = 0; $slot < $inventory->getSize(); $slot++){
            $item = $inventory->getItem($slot);
            if($item->isNull()){
                continue;
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
                    $itemToPush = $item->pop();
                    $itemInFurnace->setCount($itemInFurnace->getCount() + 1);
                }else{
                    $itemToPush = $itemInFurnace = $item->pop();
                }

                $event = new HopperTransferContainerEvent($this, $itemToPush, $inventory, $destination->getInventory());
                $event->call();
                if ($event->isCancelled()) {
                    continue;
                }

                $destination->getInventory()->setItem($slotInFurnace, $itemInFurnace);
                $inventory->setItem($slot, $item);
                return true;

            }elseif($destination instanceof TileHopper){
                $itemToPush = $item->pop();
                if(!$destination->getInventory()->canAddItem($itemToPush)){
                    continue;
                }
                // Hoppers pushing into empty hoppers set the empty hoppers transfer cooldown back to the default amount of ticks.
                if(count($destination->getInventory()->getContents()) === 0){
                    $destination->setTransferCooldown(TileHopper::DEFAULT_TRANSFER_COOLDOWN);
                }

            }elseif($destination instanceof TileJukebox){
                if(!($item instanceof Record)){
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
                $jukeboxBlock = $destination->getBlock();
                if($jukeboxBlock instanceof Jukebox){
                    $itemToPush = $item->pop();

                    $event = new HopperTransferJukeboxEvent($this, $itemToPush, $jukeboxBlock, true);
                    $event->call();
                    if ($event->isCancelled()) {
                        continue;
                    }

                    $jukeboxBlock->insertRecord($itemToPush);
                    $jukeboxBlock->getPosition()->getWorld()->setBlock($jukeboxBlock->getPosition(), $jukeboxBlock);
                    $inventory->setItem($slot, $item);
                    return true;
                }
                return false;

            }elseif($destination instanceof Container){
                $itemToPush = $item->pop();
                if(!$destination->getInventory()->canAddItem($itemToPush)){
                    continue;
                }

            }else{
                return false;
            }

            $event = new HopperTransferContainerEvent($this, $itemToPush, $inventory, $destination->getInventory());
            $event->call();
            if ($event->isCancelled()) {
                continue;
            }

            $inventory->setItem($slot, $item);
            $destination->getInventory()->addItem($itemToPush);
            return true;
        }
        return false;
    }

    /**
     * This function handles pulling items by the hopper from a container above.
     * Returns true if an item was successfully pulled or false on failure.
     */
    private function pull(HopperInventory $inventory, Inventory $origin) : bool{
        // Hoppers interact differently when pulling from different kinds of tiles.
        //TODO: Composter
        //TODO: Brewing Stand
        //TODO: Jukebox
        if($origin instanceof FurnaceInventory){
            // Hoppers either pull empty buckets from the furnace's fuel slot or pull from its result slot.
            // They prioritise pulling from the fuel slot over the result slot.
            $item = $origin->getFuel();
            if($item instanceof Bucket){
                $slot = FurnaceInventory::SLOT_FUEL;
            }else{
                $slot = FurnaceInventory::SLOT_RESULT;
                $item = $origin->getResult();
                if($item->isNull()){
                    return false;
                }
            }
            $itemToPull = $item->pop();

            $event = new HopperTransferContainerEvent($this, $itemToPull, $origin, $inventory);
            $event->call();
            if ($event->isCancelled()) {
                return false;
            }

            $origin->setItem($slot, $item);
            $inventory->addItem($itemToPull);
            return true;

        }else{
            for($slot = 0; $slot < $origin->getSize(); $slot++){
                $item = $origin->getItem($slot);
                if($item->isNull()){
                    continue;
                }
                $itemToPull = $item->pop();
                if(!$inventory->canAddItem($itemToPull)){
                    continue;
                }

                $event = new HopperTransferContainerEvent($this, $itemToPull, $origin, $inventory);
                $event->call();
                if ($event->isCancelled()) {
                    continue;
                }

                $origin->setItem($slot, $item);
                $inventory->addItem($itemToPull);
                return true;
            }
        }
        return false;
    }

    /**
     * This function handles picking up items by the hopper.
     * Returns true if an item was successfully picked up or false on failure.
     */
    private function pickup(HopperInventory $inventory) : bool{
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
            if($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity instanceof ItemEntity){
                continue;
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

            $inventory->addItem($item);
            $entity->flagForDespawn();
            return true;
        }
        return false;
    }
}