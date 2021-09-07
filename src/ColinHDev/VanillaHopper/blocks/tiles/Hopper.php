<?php

namespace ColinHDev\VanillaHopper\blocks\tiles;

use pocketmine\block\tile\Hopper as PMMP_Hopper;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class Hopper extends PMMP_Hopper {

    public const DEFAULT_TRANSFER_COOLDOWN = 8;

    private int $transferCooldown = 0;

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function getTransferCooldown() : int {
        return $this->transferCooldown;
    }

    public function setTransferCooldown(int $transferCooldown) : void {
        $this->transferCooldown = $transferCooldown;
    }
}