<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\item\Item;

/**
 * All hopper push events of this plugin extend this class.
 * TODO: Hoppers not only can push to blocks, but to entities too (for example: Minecarts).
 */
abstract class HopperPushEvent extends HopperEvent {

    private Block $destination;
    private Item $item;

    /**
     * @param Block $destination    the block the hopper is pushing to
     * @param Item  $item           the item the hopper is pushing
     */
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