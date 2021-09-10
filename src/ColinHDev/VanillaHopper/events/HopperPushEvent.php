<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\item\Item;

abstract class HopperPushEvent extends HopperEvent {

    private Block $destination;
    private Item $item;

    public function __construct(Hopper $hopper, Block $destination, Item $item) {
        parent::__construct($hopper);
        $this->destination = $destination;
        $this->item = $item;
    }

    public function getDestination() : Block {
        return $this->destination;
    }

    public function getItem() : Item {
        return clone $this->item;
    }

    public function setItem(Item $item) : void {
        $this->item = clone $item;
    }
}