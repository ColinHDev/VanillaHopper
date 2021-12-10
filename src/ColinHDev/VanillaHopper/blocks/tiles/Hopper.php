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
        // TODO: The number of items the hopper is pushing, pulling or picking up should depend on the actual delay and not on the preferred.
        $this->transferCooldown = BlockUpdateScheduler::getInstance()->scheduleDelayedBlockUpdate($this->position->getWorld(), $this->position, max(1, $transferCooldown));
    }

    public function getLastTick() : ?int {
        return $this->lastTick;
    }

    public function setLastTick(int $lastTick) : void {
        $this->lastTick = $lastTick;
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