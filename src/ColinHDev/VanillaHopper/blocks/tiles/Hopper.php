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
     * @return array<int, ItemEntity>
     */
    public function getAssignedEntities() : array {
        return $this->assignedEntities;
    }

    public function addAssignedEntity(ItemEntity $entity) : void {
        $this->assignedEntities[$entity->getId()] = $entity;
        if (count($this->assignedEntities) > 1) {
            // Unlike Java Edition, Bedrock Edition's hoppers don't save in which order item entities landed on top of
            // them to collect them in that order.
            // In Bedrock Edition hoppers collect item entities in the order in which they entered the chunk.
            // This forces us to make a sorting mechanism for our entities to ensure that the order is preserved.
            $world = $this->position->world;
            $chunkX = $this->position->x >> Chunk::COORD_BIT_SIZE;
            $chunkZ = $this->position->z >> Chunk::COORD_BIT_SIZE;
            // We need to loop over all entities that are assigned to this hopper to order them.
            // As we only need the ID of the entities, we can use uksort() in favour of uasort() since the array keys
            // are already the entity IDs.
            uksort(
                $this->assignedEntities,
                function (int $entityID1, int $entityID2) use ($world, $chunkX, $chunkZ) : int {
                    // We don't need the entities themselves, just their IDs, so we can use array_keys(). Another
                    // advantage of array_keys() is, that the function returns a list. That way, the array key of the
                    // entity, which entered the chunk first, will be 0, while the upcoming entities will have a
                    // respectively higher array key based on their order when entering the chunk.
                    $chunkEntities = array_keys($world->getChunkEntities($chunkX, $chunkZ));
                    // As item entities assign themselves to hoppers but do not unassign themselves from them, we need
                    // to check if the return of array_search() is false because the entity is not in the array
                    // because it is no longer in that chunk.
                    // As removing the entity (e.g. with removeAssignedEntity()) within this sorting function could mess
                    // up the entire sorting, we set the return value to the lowest possible (PHP_INT_MIN) so that these
                    // entities are at the beginning of the array. This way, the hopper will directly sort them out
                    // (because they are not intersecting with the hopper's pickup collision boxes), without them being
                    // stuck indefinitely in the array. We can not leave the value at "false" because when comparing
                    // both values, false would be cast to the integer 0 and would maybe not be at the beginning of the
                    // array when it is compared with the actual first entered entity of the chunk, which would also
                    // have a value of 0.
                    $chunkEnterKeyEntity1 = ($return = array_search($entityID1, $chunkEntities, true)) !== false ? $return : PHP_INT_MIN;
                    $chunkEnterKeyEntity2 = ($return = array_search($entityID2, $chunkEntities, true)) !== false ? $return : PHP_INT_MIN;
                    // If the return value is < 0, then the first element will be placed before the second in the array,
                    // if the return value is > 0, then the second is placed in front of the first. So if the first
                    // entity's position in the array of chunk entities is greater than the second ones, then the second
                    // entity entered the chunk before the first one, so we return 1 (> 0). We don't need to check if
                    // both values are the same (where we would need to return 0 to not change the order) since that
                    // could only be the case when both entities are no longer in the chunk since otherwise, all values
                    // are unique because they are keys in the list of chunk entities created by array_keys(), and
                    // therefore both values are equal PHP_INT_MIN. And it is pointless which entity is placed first in
                    // the array since both are filtered out anyway.
                    return $chunkEnterKeyEntity1 > $chunkEnterKeyEntity2 ? 1 : -1;
                }
            );
        }
    }

    public function removeAssignedEntity(ItemEntity $entity) : void {
        unset($this->assignedEntities[$entity->getId()]);
    }
}