<?php

namespace ColinHDev\VanillaHopper\entities;

use pocketmine\entity\object\ItemEntity;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;

final class ItemEntityManager {
    use SingletonTrait;

    /** @var array<int, array<int, array<int, ItemEntity>>> */
    private array $entitiesByHopper = [];

    /**
     * @return array<int, ItemEntity>
     */
    public function getEntitiesByHopper(Position $position) : array {
        $worldID = $position->world->getId();
        if (!isset($this->entitiesByHopper[$worldID])) {
            return [];
        }
        $blockHash = World::blockHash((int) floor($position->x), (int) floor($position->y), (int) floor($position->z));
        if (!isset($this->entitiesByHopper[$worldID][$blockHash])) {
            return [];
        }
        return $this->entitiesByHopper[$worldID][$blockHash];
    }

    public function addEntityToHopper(Position $position, ItemEntity $entity) : void {
        $worldID = $position->world->getId();
        if (!isset($this->entitiesByHopper[$worldID])) {
            $this->entitiesByHopper[$worldID] = [];
        }
        $blockHash = World::blockHash($position->x, $position->y, $position->z);
        if (!isset($this->entitiesByHopper[$worldID][$blockHash])) {
            $this->entitiesByHopper[$worldID][$blockHash] = [];
        }
        $this->entitiesByHopper[$worldID][$blockHash][$entity->getId()] = $entity;
        if (count($this->entitiesByHopper[$worldID][$blockHash]) > 1) {
            // Unlike Java Edition, Bedrock Edition's hoppers don't save in which order item entities landed on top of them to collect them in that order.
            // In Bedrock Edition hoppers collect item entities in the order in which they entered the chunk.
            $world = $position->world;
            $chunkX = $position->x >> Chunk::COORD_BIT_SIZE;
            $chunkZ = $position->z >> Chunk::COORD_BIT_SIZE;
            uksort(
                $this->entitiesByHopper[$worldID][$blockHash],
                function (int $entityID1, int $entityID2) use ($world, $chunkX, $chunkZ) : int {
                    $chunkEntities = array_keys($world->getChunkEntities($chunkX, $chunkZ));
                    return array_search($entityID1, $chunkEntities, true) > array_search($entityID2, $chunkEntities, true) ? 1 : -1;
                }
            );
        }
    }

    public function removeEntityFromHopper(Position $position, ItemEntity $entity) : void {
        $worldID = $position->world->getId();
        $blockHash = World::blockHash((int) floor($position->x), (int) floor($position->y), (int) floor($position->z));
        unset($this->entitiesByHopper[$worldID][$blockHash][$entity->getId()]);
        if (empty($this->entitiesByHopper[$worldID][$blockHash])) {
            unset($this->entitiesByHopper[$worldID][$blockHash]);
            if (empty($this->entitiesByHopper[$worldID])) {
                unset($this->entitiesByHopper[$worldID]);
            }
        }
    }
}