<?php

namespace ColinHDev\VanillaHopper\blocks\tiles;

use ColinHDev\VanillaHopper\blocks\BlockUpdateScheduler;
use ColinHDev\VanillaHopper\entities\ItemEntity;
use pocketmine\block\tile\Hopper as PMMP_Hopper;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class Hopper extends PMMP_Hopper {

    private int $transferCooldown = 0;
    private ?int $lastTick = null;
    private bool $isScheduledForDelayedBlockUpdate = true;
    /** @var AxisAlignedBB[] | null */
    private ?array $pickupCollisionBoxes = null;
    /** @var array<int, ItemEntity> */
    private array $assignedEntities = [];

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

    /**
     * @return array<int, ItemEntity>
     */
    public function getAssignedEntities() : array {
        return $this->assignedEntities;
    }

    public function addAssignedEntity(ItemEntity $entity) : void {
        $this->assignedEntities[$entity->getId()] = $entity;
        if (count($this->assignedEntities) > 1) {
            // Unlike Java Edition, Bedrock Edition's hoppers don't save in which order item entities landed on top of them to collect them in that order.
            // In Bedrock Edition hoppers collect item entities in the order in which they entered the chunk.
            $world = $this->position->world;
            $chunkX = $this->position->x >> Chunk::COORD_BIT_SIZE;
            $chunkZ = $this->position->z >> Chunk::COORD_BIT_SIZE;
            uksort(
                $this->assignedEntities,
                function (int $entityID1, int $entityID2) use ($world, $chunkX, $chunkZ) : int {
                    $chunkEntities = array_keys($world->getChunkEntities($chunkX, $chunkZ));
                    return array_search($entityID1, $chunkEntities, true) > array_search($entityID2, $chunkEntities, true) ? 1 : -1;
                }
            );
        }
    }

    public function removeAssignedEntity(ItemEntity $entity) : void {
        unset($this->assignedEntities[$entity->getId()]);
    }
}