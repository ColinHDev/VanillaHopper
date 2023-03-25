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
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\tile\TileFactory;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

class VanillaHopper extends PluginBase {
    use SingletonTrait;

    public function onEnable() : void {
        self::setInstance($this);

        RuntimeBlockStateRegistry::getInstance()->register(new Hopper(), true);

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
            ['Item', 'minecraft:item']
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
}