<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\item\Item;

abstract class HopperPushEvent extends HopperEvent {

    private Block $destination;
    private Item $item;

    public function __construct(Hopper $block, HopperInventory $inventory, Block $destination, Item $item) {
        parent::__construct($block, $inventory);
        $this->destination = $destination;
        $this->item = $item;
    }

    public function getDestination() : Block {
        return $this->destination;
    }

    public function getItem() : Item {
        return clone $this->item;
    }
}