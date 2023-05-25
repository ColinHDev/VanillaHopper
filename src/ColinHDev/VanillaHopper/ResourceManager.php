<?php

namespace ColinHDev\VanillaHopper;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use function is_numeric;

class ResourceManager {
    use SingletonTrait;

    private int $defaultTransferCooldown;
    private int $itemsPerUpdate;
    private int $updatesPerTick;

    public function __construct() {
        VanillaHopper::getInstance()->saveResource("config.yml");
        $config = new Config(VanillaHopper::getInstance()->getDataFolder() . "config.yml", Config::YAML);

        $defaultTransferCooldown = $config->get("hopper.transferCooldown");
        $this->defaultTransferCooldown = max(1, is_numeric($defaultTransferCooldown) ? (int) $defaultTransferCooldown : 8);
        $itemsPerUpdate = $config->get("hopper.itemsPerUpdate");
        $this->itemsPerUpdate = max(1, is_numeric($itemsPerUpdate) ? (int) $itemsPerUpdate : 1);
        $updatesPerTick = $config->get("hopper.updatesPerTick");
        $this->updatesPerTick = is_numeric($updatesPerTick) ? (int) $updatesPerTick : -1;
    }

    public function getDefaultTransferCooldown() : int {
        return $this->defaultTransferCooldown;
    }

    public function getItemsPerUpdate() : int {
        return $this->itemsPerUpdate;
    }

    public function getUpdatesPerTick() : int {
        return $this->updatesPerTick;
    }
}