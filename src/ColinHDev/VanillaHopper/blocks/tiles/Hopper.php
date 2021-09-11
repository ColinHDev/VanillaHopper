<?php

namespace ColinHDev\VanillaHopper\blocks\tiles;

use pocketmine\block\tile\Hopper as PMMP_Hopper;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class Hopper extends PMMP_Hopper {

    private int $transferCooldown = 0;
    private ?int $lastTick = null;

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function getTransferCooldown() : int {
        return $this->transferCooldown;
    }

    public function setTransferCooldown(int $transferCooldown) : void {
        $this->transferCooldown = $transferCooldown;
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, max(1, $transferCooldown));
    }

    public function getLastTick() : ?int {
        return $this->lastTick;
    }

    public function setLastTick(int $lastTick) : void {
        $this->lastTick = $lastTick;
    }
}