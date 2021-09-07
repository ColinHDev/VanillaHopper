<?php

namespace ColinHDev\VanillaHopper;

use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper as TileHopper;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\plugin\PluginBase;

class VanillaHopper extends PluginBase {

    public function onLoad() : void {
        // overwrite Hopper block
        $oldHopper = VanillaBlocks::HOPPER();
        BlockFactory::getInstance()->register(
            new Hopper(
                new BlockIdentifier($oldHopper->getIdInfo()->getBlockId(), $oldHopper->getIdInfo()->getVariant(), $oldHopper->getIdInfo()->getItemId(), TileHopper::class),
                $oldHopper->getName(),
                $oldHopper->getBreakInfo()
            ),
            true
        );
        // overwrite Hopper tile
        TileFactory::getInstance()->register(TileHopper::class, ["Hopper", "minecraft:hopper"]);
    }
}