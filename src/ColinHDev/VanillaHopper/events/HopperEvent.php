<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

abstract class HopperEvent extends Event implements Cancellable {
    use CancellableTrait;

    private Hopper $hopper;

    public function __construct(Hopper $hopper) {
        $this->hopper = $hopper;
    }

    public function getHopper() : Hopper {
        return $this->hopper;
    }
}