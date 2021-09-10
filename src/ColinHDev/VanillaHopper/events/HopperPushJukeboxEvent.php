<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\Jukebox;
use pocketmine\item\Record;

/**
 * Called when a hopper tries to push a record into a jukebox.
 */
class HopperPushJukeboxEvent extends HopperPushEvent {

    private Jukebox $destination;
    private Record $item;

    /**
     * @param Jukebox   $destination    the jukebox the hopper is pushing to
     * @param Record    $item           the record the hopper is pushing
     */
    public function __construct(Hopper $block, HopperInventory $inventory, Jukebox $destination, Record $item) {
        parent::__construct($block, $inventory, $destination, $item);
        $this->destination = $destination;
        $this->item = $item;
    }

    public function getDestination() : Jukebox {
        return $this->destination;
    }

    public function getItem() : Record {
        return clone $this->item;
    }
}