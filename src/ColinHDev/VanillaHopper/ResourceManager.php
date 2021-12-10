<?php

namespace ColinHDev\VanillaHopper;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class ResourceManager {
    use SingletonTrait;

    private int $defaultTransferCooldown;
    private int $itemsPerUpdate;
    private int $updatesPerTick;

    public function __construct() {
        VanillaHopper::getInstance()->saveResource("config.yml");
        $config = new Config(VanillaHopper::getInstance()->getDataFolder() . "config.yml", Config::YAML);

        $this->defaultTransferCooldown = max(1, (int) $config->get("hopper.transferCooldown", 8));
        $this->itemsPerUpdate = max(1, (int) $config->get("hopper.itemsPerUpdate", 1));
        $this->updatesPerTick = (int) $config->get("hopper.updatesPerTick", -1);
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