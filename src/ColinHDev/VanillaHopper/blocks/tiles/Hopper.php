<?php

namespace ColinHDev\VanillaHopper\blocks\tiles;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use pocketmine\block\tile\Hopper as PMMP_Hopper;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class Hopper extends PMMP_Hopper {

    private int $transferCooldown = 0;
    private ?int $lastTick = null;
    private bool $isScheduledForDelayedBlockUpdate = true;
    /** @var AxisAlignedBB[] | null */
    private ?array $pickupCollisionBoxes = null;

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        BlockUpdateScheduler::getInstance()->scheduleDelayedBlockUpdate($this->position->getWorld(), $this->position, 1);
    }

    public function getTransferCooldown() : int {
        return $this->transferCooldown;
    }

    public function setTransferCooldown(int $transferCooldown) : void {
        $this->transferCooldown = $transferCooldown;
    }

    public function getLastTick() : ?int {
        return $this->lastTick;
    }

    public function setLastTick(int $lastTick) : void {
        $this->lastTick = $lastTick;
    }

    public function isScheduledForDelayedBlockUpdate() : bool {
        // An earlier approach was the following:
        // return $this->transferCooldown > 0 && $this->lastTick !== null;
        // The problem with that is that it was not possible to schedule a block update for the current tick
        // (transfer cooldown of zero) with the output of the method being changed to true.
        return $this->isScheduledForDelayedBlockUpdate;
    }

    public function setScheduledForDelayedBlockUpdate(bool $isScheduledForDelayedBlockUpdate) : void {
        $this->isScheduledForDelayedBlockUpdate = $isScheduledForDelayedBlockUpdate;
    }

    /**
     * @return AxisAlignedBB[] | null
     */
    public function getPickupCollisionBoxes() : ?array {
        return $this->pickupCollisionBoxes;
    }

    /**
     * @param AxisAlignedBB[] $pickupCollisionBoxes
     */
    public function setPickupCollisionBoxes(array $pickupCollisionBoxes) : void {
        $this->pickupCollisionBoxes = $pickupCollisionBoxes;
    }
}