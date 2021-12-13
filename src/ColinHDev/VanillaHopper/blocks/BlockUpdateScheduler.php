<?php

namespace ColinHDev\VanillaHopper\blocks;

use ColinHDev\VanillaHopper\ResourceManager;
use pocketmine\math\Vector3;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

final class BlockUpdateScheduler {
    use SingletonTrait;

    private int $currentTick = 0;
    private int $maxUpdatesPerTick;
    /** @var array<int, int> */
    private array $updatesPerTick = [];

    public function __construct() {
        $this->maxUpdatesPerTick = ResourceManager::getInstance()->getUpdatesPerTick();
    }

    /**
     * @param int $preferredDelay the preferred delay, not guaranteed that the block update will be scheduled on the tick in that delay
     * Returns the actual block update delay.
     */
    public function scheduleDelayedBlockUpdate(World $world, Vector3 $vector3, int $preferredDelay) : int {
        $actualDelay = $preferredDelay;
        // Skip the following checks if it is disabled anyway.
        if ($this->maxUpdatesPerTick > 0) {

            $currentTick = $world->getServer()->getTick();
            if ($this->currentTick !== $currentTick) {
                $this->currentTick = $currentTick;
                // Remove every tick of array that already happened.
                // We can't remove the current tick, as ItemEntityManager runs a scheduled task and as seeable in
                // Server::tick(), are schedulers ticked before the world. Therefore it is still possible to schedule
                // a block update for the current tick.
                foreach ($this->updatesPerTick as $tick => $updates) {
                    if ($tick < $currentTick) {
                        unset($this->updatesPerTick[$tick]);
                        continue;
                    }
                    break;
                }
            }
            $delayTick = $currentTick + $preferredDelay;

            // If no block updates are planned for the preferred tick, we can just schedule it then.
            if (!isset($this->updatesPerTick[$delayTick])) {
                $this->updatesPerTick[$delayTick] = 1;

            // As long as the max block updates per tick aren't reached, we can just schedule it at that tick.
            } elseif ($this->updatesPerTick[$delayTick] < $this->maxUpdatesPerTick) {
                $this->updatesPerTick[$delayTick]++;

            } else {
                // Loop through the updates to find the next tick which is not on the max value.
                foreach ($this->updatesPerTick as $tick => $updates) {
                    // Skip this tick because it is too early and before our preferred tick.
                    if ($tick <= $delayTick) {
                        continue;
                    }
                    // Skip this tick because it's already on the max value.
                    if ($updates >= $this->maxUpdatesPerTick) {
                        continue;
                    }

                    // This tick is not on the max value and therefore we can schedule the block update at it.
                    $delayTick = $tick;
                    $actualDelay = $delayTick - $currentTick;
                    break;
                }
                // If every tick in the array is on the max value, it could not found a suitable tick and change the $actualDelay variable.
                if ($actualDelay === $preferredDelay) {
                    // Therefore the next possible tick would be after the last tick in the array.
                    $delayTick = array_key_last($this->updatesPerTick) + 1;
                    $this->updatesPerTick[$delayTick] = 1;
                    $actualDelay = $delayTick - $currentTick;
                } else {
                    $this->updatesPerTick[$delayTick]++;
                }
            }
        }
        // TODO: If a block update is already scheduled for that block before our tick, the block update for our tick is not scheduled.
        //  If that is the case, it would fill our block update counter falsely and lead to not fully using the capacity of the queue.
        // This is currently the case if a hopper is pushing into another hopper. If the hopper is empty, it would be set on cooldown.
        // But if the hopper already has scheduled a block update, the new and later scheduled update won't be stored by the world.
        // This would falsely increase the update counter of that tick by one.
        $world->scheduleDelayedBlockUpdate($vector3, $actualDelay);
        return $actualDelay;
    }
}