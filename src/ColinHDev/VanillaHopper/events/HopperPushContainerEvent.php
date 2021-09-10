<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperPushContainerEvent extends HopperPushEvent {

    private Inventory $hopperInventory;
    private Inventory $destinationInventory;

    public function __construct(Hopper $hopper, Block $destination, Item $item, HopperInventory $hopperInventory, Inventory $destinationInventory) {
        parent::__construct($hopper, $destination, $item);
        $this->hopperInventory = $hopperInventory;
        $this->destinationInventory = $destinationInventory;
    }

    public function getHopperInventory() : Inventory {
        return $this->hopperInventory;
    }

    public function setHopperInventory(Inventory $hopperInventory) : void {
        $this->hopperInventory = $hopperInventory;
    }

    public function getDestinationInventory() : Inventory {
        return $this->destinationInventory;
    }

    public function setDestinationInventory(Inventory $destinationInventory) : void {
        $this->destinationInventory = $destinationInventory;
    }
}