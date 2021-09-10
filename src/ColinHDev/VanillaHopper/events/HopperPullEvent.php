<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Block;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\item\Item;

/**
 * All hopper pull events of this plugin extend this class.
 * TODO: Hoppers not only can pull from blocks, but from entities too (for example: Minecarts).
 */
abstract class HopperPullEvent extends HopperEvent {

    private Block $origin;
    private Item $item;

    /**
     * @param Block $origin the block the hopper is pulling from
     * @param Item  $item   the item the hopper is pulling
     */
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