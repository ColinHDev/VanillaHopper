<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperTransferContainerEvent extends BlockEvent implements Cancellable {
    use CancellableTrait;

    private Item $item;
    private Inventory $origin;
    private Inventory $destination;

    public function __construct(Hopper $hopper, Item $item, Inventory $origin, Inventory $destination) {
        parent::__construct($hopper);
        $this->item = $item;
        $this->origin = $origin;
        $this->destination = $destination;
    }

    public function getItem() : Item {
        return clone $this->item;
    }

    public function setItem(Item $item) : void {
        $this->item = clone $item;
    }

    public function getOrigin() : Inventory {
        return $this->origin;
    }

    public function setOrigin(Inventory $origin) : void {
        $this->origin = $origin;
    }

    public function getDestination() : Inventory {
        return $this->destination;
    }

    public function setDestination(Inventory $destination) : void {
        $this->destination = $destination;
    }
}