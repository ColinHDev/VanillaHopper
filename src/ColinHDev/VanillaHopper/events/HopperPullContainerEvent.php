<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperPullContainerEvent extends HopperPullEvent {

    private HopperInventory $hopperInventory;
    private Inventory $originInventory;

    public function __construct(Hopper $hopper, Block $origin, Item $item, HopperInventory $hopperInventory, Inventory $originInventory) {
        parent::__construct($hopper, $origin, $item);
        $this->hopperInventory = $hopperInventory;
        $this->originInventory = $originInventory;
    }

    public function getHopperInventory() : HopperInventory {
        return $this->hopperInventory;
    }

    public function setHopperInventory(HopperInventory $hopperInventory) : void {
        $this->hopperInventory = $hopperInventory;
    }

    public function getOriginInventory() : Inventory {
        return $this->originInventory;
    }

    public function setOriginInventory(Inventory $originInventory) : void {
        $this->originInventory = $originInventory;
    }
}