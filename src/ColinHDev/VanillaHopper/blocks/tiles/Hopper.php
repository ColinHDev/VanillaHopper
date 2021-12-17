<?php

namespace ColinHDev\VanillaHopper\blocks\tiles;

use ColinHDev\VanillaHopper\blocks\BlockDataStorer;
use ColinHDev\VanillaHopper\blocks\Hopper as HopperBlock;
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
        $block = $this->getBlock();
        if ($block instanceof HopperBlock) {
            $block->scheduleDelayedBlockUpdate($this->transferCooldown);
        }
    }

    public function close() : void {
        parent::close();
        BlockDataStorer::getInstance()->removeBlock($this->position);
    }
}