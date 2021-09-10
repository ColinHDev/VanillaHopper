<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\item\Item;

abstract class HopperPullEvent extends HopperEvent {

    private Block $origin;
    private Item $item;

    public function __construct(Hopper $block, HopperInventory $inventory, Block $origin, Item $item) {
        parent::__construct($block, $inventory);
        $this->origin = $origin;
        $this->item = $item;
    }

    public function getOrigin() : Block {
        return $this->origin;
    }

    public function getItem() : Item {
        return clone $this->item;
    }
}