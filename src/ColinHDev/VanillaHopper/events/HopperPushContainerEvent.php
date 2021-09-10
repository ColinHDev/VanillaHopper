<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperPushContainerEvent extends HopperPushEvent {

    private Inventory $destinationInventory;

    public function __construct(Hopper $block, HopperInventory $inventory, Block $destination, Inventory $destinationInventory, Item $item) {
        parent::__construct($block, $inventory, $destination, $item);
        $this->destinationInventory = $destinationInventory;
    }

    public function getDestinationInventory() : Inventory {
        return $this->destinationInventory;
    }
}