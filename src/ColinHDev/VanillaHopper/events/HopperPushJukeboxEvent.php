<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Jukebox;
use pocketmine\item\Item;
use pocketmine\item\Record;

class HopperPushJukeboxEvent extends HopperPushEvent {

    private Jukebox $destination;
    private Record $item;

    public function __construct(Hopper $hopper, Jukebox $destination, Record $item) {
        parent::__construct($hopper, $destination, $item);
        $this->destination = $destination;
        $this->item = $item;
    }

    public function getDestination() : Jukebox {
        return $this->destination;
    }

    public function getItem() : Record {
        return clone $this->item;
    }

    public function setItem(Item $item) : void {
        if (!$item instanceof Record) {
            throw new \TypeError("Expected type of item to be an instance of Record, " . get_class($item) . " given.");
        }
        $this->item = clone $item;
    }
}