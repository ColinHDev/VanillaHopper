<?php

namespace ColinHDev\VanillaHopper\blocks;

use ColinHDev\VanillaHopper\entities\ItemEntity;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;

/**
 * This is a utility class meant for storing specific data our hoppers need to work.
 * Originally these data were stored by the tiles, but this idea was scrapped as tiles should not be used anymore in
 * PocketMine-MP.
 */
final class BlockDataStorer {
    use SingletonTrait;

    /** @var array<int, array<int, array<int, int>>> */
    private array $lastTicks = [];
    /** @var array<int, array<int, array<int, int>>> */
    private array $nextTicks = [];
    /** @var array<int, array<int, array<int, ItemEntity[]>>> */
    private array $assignedEntities = [];

    /**
     * Returns the tick a hopper received its last update or null if it was never updated since its loading.
     */
    public function getLastTick(Position $position) : ?int {
        $worldID = $position->getWorld()->getId();
        $chunkHash = World::chunkHash($position->x >> Chunk::COORD_BIT_SIZE, $position->z >> Chunk::COORD_BIT_SIZE);
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        if (isset($this->lastTicks[$worldID][$chunkHash][$relativeBlockHash])) {
            return $this->lastTicks[$worldID][$chunkHash][$relativeBlockHash];
        }
        return null;
    }

    /**
     * Set the tick, on which a hopper received its last update.
     */
    public function setLastTick(Position $position, int $lastTick) : void {
        $worldID = $position->getWorld()->getId();
        if (!isset($this->lastTicks[$worldID])) {
            $this->lastTicks[$worldID] = [];
        }
        $chunkHash = World::chunkHash($position->x >> Chunk::COORD_BIT_SIZE, $position->z >> Chunk::COORD_BIT_SIZE);
        if (!isset($this->lastTicks[$worldID][$chunkHash])) {
            $this->lastTicks[$worldID][$chunkHash] = [];
        }
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        $this->lastTicks[$worldID][$chunkHash][$relativeBlockHash] = $lastTick;
    }

    /**
     * Returns the tick a hopper will receive its next update or null if the hopper is currently not scheduled for an
     * update.
     */
    public function getNextTick(Position $position) : ?int {
        $worldID = $position->getWorld()->getId();
        $chunkHash = World::chunkHash($position->x >> Chunk::COORD_BIT_SIZE, $position->z >> Chunk::COORD_BIT_SIZE);
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        if (isset($this->nextTicks[$worldID][$chunkHash][$relativeBlockHash])) {
            return $this->nextTicks[$worldID][$chunkHash][$relativeBlockHash];
        }
        return null;
    }

    /**
     * Set the tick, on which a hopper will receive its next update or null if the hopper will not be scheduled for
     * another update.
     */
    public function setNextTick(Position $position, ?int $nextTick) : void {
        $worldID = $position->getWorld()->getId();
        if (!isset($this->nextTicks[$worldID])) {
            $this->nextTicks[$worldID] = [];
        }
        $chunkHash = World::chunkHash($position->x >> Chunk::COORD_BIT_SIZE, $position->z >> Chunk::COORD_BIT_SIZE);
        if (!isset($this->nextTicks[$worldID][$chunkHash])) {
            $this->nextTicks[$worldID][$chunkHash] = [];
        }
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        if ($nextTick === null) {
            unset($this->nextTicks[$worldID][$chunkHash][$relativeBlockHash]);
        } else {
            $this->nextTicks[$worldID][$chunkHash][$relativeBlockHash] = $nextTick;
        }
    }

    /**
     * Returns all item entities that are assigned to the block on a specific position.
     * @return array<int, ItemEntity>
     */
    public function getAssignedEntities(Position $position) : array {
        $worldID = $position->getWorld()->getId();
        $chunkHash = World::chunkHash($position->x >> Chunk::COORD_BIT_SIZE, $position->z >> Chunk::COORD_BIT_SIZE);
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        if (isset($this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash])) {
            return $this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash];
        }
        return [];
    }

    /**
     * Assign an item entity to a block at a specific position.
     */
    public function assignEntity(Position $position, ItemEntity $entity) : void {
        $world = $position->getWorld();
        $worldID = $world->getId();
        if (!isset($this->assignedEntities[$worldID])) {
            $this->assignedEntities[$worldID] = [];
        }
        $chunkX = $position->x >> Chunk::COORD_BIT_SIZE;
        $chunkZ = $position->z >> Chunk::COORD_BIT_SIZE;
        $chunkHash = World::chunkHash($chunkX, $chunkZ);
        if (!isset($this->assignedEntities[$worldID][$chunkHash])) {
            $this->assignedEntities[$worldID][$chunkHash] = [];
        }
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        if (!isset($this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash])) {
            $this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash] = [];
        }
        $this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash][$entity->getId()] = $entity;
        if (count($this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash]) > 1) {
            // Unlike Java Edition, Bedrock Edition's hoppers don't save in which order item entities landed on top of
            // them to collect them in that order.
            // In Bedrock Edition hoppers collect item entities in the order in which they entered the chunk.
            // This forces us to make a sorting mechanism for our entities to ensure that the order is preserved.
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

    /**
     * Unassign an item entity from a block at a specific position.
     */
    public function unassignEntity(Position $position, ItemEntity $entity) : void {
        $worldID = $position->getWorld()->getId();
        $chunkHash = World::chunkHash($position->x >> Chunk::COORD_BIT_SIZE, $position->z >> Chunk::COORD_BIT_SIZE);
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        unset($this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash][$entity->getId()]);
        $this->cleanArrays();
    }

    /**
     * Remove a block to remove all data assigned to it, e.g. when the block is broken.
     */
    public function removeBlock(Position $position) : void {
        $worldID = $position->getWorld()->getId();
        $chunkHash = World::chunkHash($position->x >> Chunk::COORD_BIT_SIZE, $position->z >> Chunk::COORD_BIT_SIZE);
        $relativeBlockHash = World::chunkBlockHash($position->x & Chunk::COORD_MASK, $position->y & Chunk::COORD_MASK, $position->z & Chunk::COORD_MASK);
        unset($this->lastTicks[$worldID][$chunkHash][$relativeBlockHash]);
        unset($this->nextTicks[$worldID][$chunkHash][$relativeBlockHash]);
        unset($this->assignedEntities[$worldID][$chunkHash][$relativeBlockHash]);
        $this->cleanArrays();
    }

    /**
     * Remove a chunk of a world to remove all data assigned to that chunk, e.g. when the chunk unloads.
     */
    public function removeChunk(World $world, int $chunkX, int $chunkZ) : void {
        $worldID = $world->getId();
        $chunkHash = World::chunkHash($chunkX, $chunkZ);
        unset($this->lastTicks[$worldID][$chunkHash]);
        unset($this->nextTicks[$worldID][$chunkHash]);
        unset($this->assignedEntities[$worldID][$chunkHash]);
        $this->cleanArrays();
    }

    /**
     * Remove a world to remove all data assigned to it, e.g. when the world unloads.
     */
    public function removeWorld(World $world) : void {
        $worldID = $world->getId();
        unset($this->lastTicks[$worldID]);
        unset($this->nextTicks[$worldID]);
        unset($this->assignedEntities[$worldID]);
    }

    /**
     * As this class holds multiple multi-dimensional arrays of various data, there could be single empty arrays
     * inside them.
     * This method cleans all of our arrays from those.
     */
    public function cleanArrays() : void {
        foreach ($this->lastTicks as $worldID => &$chunks) {
            foreach ($chunks as $chunkHash => $blocks) {
                if (count($blocks) === 0) {
                    unset($chunks[$chunkHash]);
                }
            }
            if (count($chunks) === 0) {
                unset($this->lastTicks[$worldID]);
            }
        }
        unset($chunks);

        foreach ($this->nextTicks as $worldID => &$chunks) {
            foreach ($chunks as $chunkHash => $blocks) {
                if (count($blocks) === 0) {
                    unset($chunks[$chunkHash]);
                }
            }
            if (count($chunks) === 0) {
                unset($this->nextTicks[$worldID]);
            }
        }
        unset($chunks);

        foreach ($this->assignedEntities as $worldID => &$chunks) {
            foreach ($chunks as $chunkHash => &$blocks) {
                foreach ($blocks as $blockHash => $entities) {
                    if (count($entities) === 0) {
                        unset($blocks[$blockHash]);
                    }
                }
                if (count($blocks) === 0) {
                    unset($chunks[$chunkHash]);
                }
            }
            unset($blocks);
            if (count($chunks) === 0) {
                unset($this->assignedEntities[$worldID]);
            }
        }
        unset($chunks);
    }
}