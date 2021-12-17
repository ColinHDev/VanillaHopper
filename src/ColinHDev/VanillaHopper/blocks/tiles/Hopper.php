<?php

namespace ColinHDev\VanillaHopper\blocks\tiles;

use ColinHDev\VanillaHopper\blocks\BlockDataStorer;
use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use pocketmine\block\tile\Hopper as PMMPHopper;
use pocketmine\nbt\tag\CompoundTag;

class Hopper extends PMMPHopper {

    private int $transferCooldown = 0;

    public function getTransferCooldown() : int {
        return $this->transferCooldown;
    }

    public function setTransferCooldown(int $transferCooldown) : void {
        $this->transferCooldown = $transferCooldown;
    }

    public function readSaveData(CompoundTag $nbt) : void {
        parent::readSaveData($nbt);
        BlockUpdateScheduler::getInstance()->scheduleDelayedBlockUpdate($this->position->getWorld(), $this->position, $this->transferCooldown);
    }

    public function close() : void {
        parent::close();
        BlockDataStorer::getInstance()->removeBlock($this->position);
    }
}