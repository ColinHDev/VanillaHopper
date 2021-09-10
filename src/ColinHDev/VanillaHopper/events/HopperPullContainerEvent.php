<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperPullContainerEvent extends HopperPullEvent {

    private Inventory $originInventory;

    public function __construct(Hopper $block, HopperInventory $inventory, Block $origin, Inventory $originInventory, Item $item) {
        parent::__construct($block, $inventory, $origin, $item);
        $this->originInventory = $originInventory;
    }

    public function getOriginInventory() : Inventory {
        return $this->originInventory;
    }
}