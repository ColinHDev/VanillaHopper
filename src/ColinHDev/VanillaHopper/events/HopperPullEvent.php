<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\item\Item;

abstract class HopperPullEvent extends HopperEvent {

    private Block $origin;
    private Item $item;

    public function __construct(Hopper $hopper, Block $origin, Item $item) {
        parent::__construct($hopper);
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