<?php

namespace ColinHDev\VanillaHopper;

use ColinHDev\VanillaHopper\blocks\Hopper;
use ColinHDev\VanillaHopper\blocks\tiles\Hopper as TileHopper;
use ColinHDev\VanillaHopper\entities\ItemEntity;
use ColinHDev\VanillaHopper\listeners\BlockItemPickupListener;
use ColinHDev\VanillaHopper\listeners\ChunkUnloadListener;
use ColinHDev\VanillaHopper\listeners\EntitySpawnListener;
use ColinHDev\VanillaHopper\listeners\FurnaceBurnListener;
use ColinHDev\VanillaHopper\listeners\FurnaceSmeltListener;
use ColinHDev\VanillaHopper\listeners\HopperPullListener;
use ColinHDev\VanillaHopper\listeners\HopperPushListener;
use ColinHDev\VanillaHopper\listeners\InventoryTransactionListener;
use ColinHDev\VanillaHopper\listeners\WorldUnloadListener;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

class VanillaHopper extends PluginBase {

    private static VanillaHopper $instance;

    public function onEnable() : void {
        self::$instance = $this;

        $oldHopper = VanillaBlocks::HOPPER();
        BlockFactory::getInstance()->register(
            new Hopper(
                new BlockIdentifier($oldHopper->getIdInfo()->getBlockId(), $oldHopper->getIdInfo()->getVariant(), $oldHopper->getIdInfo()->getItemId(), TileHopper::class),
                $oldHopper->getName(),
                $oldHopper->getBreakInfo()
            ),
            true
        );

        TileFactory::getInstance()->register(TileHopper::class, ["Hopper", "minecraft:hopper"]);

        EntityFactory::getInstance()->register(
            ItemEntity::class,
            function (World $world, CompoundTag $nbt) : ItemEntity {
                $itemTag = $nbt->getCompoundTag("Item");
                if($itemTag === null){
                    throw new \UnexpectedValueException("Expected \"Item\" NBT tag not found");
                }

                $item = Item::nbtDeserialize($itemTag);
                if($item->isNull()){
                    throw new \UnexpectedValueException("Item is invalid");
                }
                return new ItemEntity(EntityDataHelper::parseLocation($nbt, $world), $item, $nbt);
            },
            ['Item', 'minecraft:item'],
            EntityLegacyIds::ITEM
        );

        $this->getServer()->getPluginManager()->registerEvents(new BlockItemPickupListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new ChunkUnloadListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EntitySpawnListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new FurnaceBurnListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new FurnaceSmeltListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new HopperPullListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new HopperPushListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new InventoryTransactionListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new WorldUnloadListener(), $this);
    }

    public static function getInstance() : VanillaHopper {
        return self::$instance;
    }
}