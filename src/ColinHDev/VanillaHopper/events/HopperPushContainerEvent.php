<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

/**
 * Called when a hopper tries to push an item into a block's inventory.
 */
class HopperPushContainerEvent extends HopperPushEvent {

    private Inventory $destinationInventory;

    /**
     * @param Inventory $destinationInventory the inventory of the block the hopper is pushing to
     */
    public function __construct(Hopper $block, HopperInventory $inventory, Block $destination, Inventory $destinationInventory, Item $item) {
        parent::__construct($block, $inventory, $destination, $item);
        $this->destinationInventory = $destinationInventory;
    }

    public function getDestinationInventory() : Inventory {
        return $this->destinationInventory;
    }
}