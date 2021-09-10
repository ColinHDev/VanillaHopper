<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

/**
 * All hopper related events of this plugin extend this class.
 */
abstract class HopperEvent extends Event implements Cancellable {
    use CancellableTrait;

    protected Hopper $block;
    protected HopperInventory $inventory;

    /**
     * @param Hopper            $block      the hopper causing the event
     * @param HopperInventory   $inventory  the inventory of the hopper
     */
    public function __construct(Hopper $block, HopperInventory $inventory) {
        $this->block = $block;
        $this->inventory = $inventory;
    }

    public function getBlock() : Hopper {
        return $this->block;
    }

    public function getInventory() : HopperInventory {
        return $this->inventory;
    }
}