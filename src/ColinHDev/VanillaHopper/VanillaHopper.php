<?php

declare(strict_types=1);

namespace ColinHDev\VanillaHopper;

use Closure;
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
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\CraftingManagerFromDataHelper;
use pocketmine\data\bedrock\item\ItemDeserializer;
use pocketmine\data\bedrock\item\ItemSerializer;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\World;
use ReflectionClass;
use Symfony\Component\Filesystem\Path;
use function mb_strtoupper;

class VanillaHopper extends PluginBase {
    use SingletonTrait;

    public function onEnable() : void {
        self::setInstance($this);

        $this->registerHopperBlock();

        TileFactory::getInstance()->register(TileHopper::class, ["Hopper", "minecraft:hopper"]);

        EntityFactory::getInstance()->register(
            ItemEntity::class,
            function (World $world, CompoundTag $nbt) : ItemEntity {
                $itemTag = $nbt->getCompoundTag(ItemEntity::TAG_ITEM);
                if($itemTag === null){
                    throw new SavedDataLoadingException("Expected \"" . ItemEntity::TAG_ITEM . "\" NBT tag not found");
                }

                $item = Item::nbtDeserialize($itemTag);
                if($item->isNull()){
                    throw new SavedDataLoadingException("Item is invalid");
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

    private function registerHopperBlock() : void {
        $oldHopper = VanillaBlocks::HOPPER();
        $newHopper = new Hopper(
            new BlockIdentifier($oldHopper->getTypeId(), TileHopper::class),
            $oldHopper->getName(),
            new BlockTypeInfo($oldHopper->getBreakInfo(), $oldHopper->getTypeTags())
        );

        /**
         * Overwriting the entry in the RuntimeBlockStateRegistry by calling our custom version of its 
         * @see RuntimeBlockStateRegistry::register() method without prohibiting the overwriting of existing entries
         */
        (function(Hopper $block) : void {
            $typeId = $block->getTypeId();
            $this->typeIndex[$typeId] = clone $block;
            foreach($block->generateStatePermutations() as $v){
                $this->fillStaticArrays($v->getStateId(), $v);
            }
        })->call(RuntimeBlockStateRegistry::getInstance(), $newHopper);

        $reflection = new ReflectionClass(VanillaBlocks::class);
        /** @var array<string, Block> $blocks */
        $blocks = $reflection->getStaticPropertyValue("members");
        $blocks[mb_strtoupper("hopper")] = clone $newHopper;
        $reflection->setStaticPropertyValue("members", $blocks);

        /**
         * Overwriting the entry in the ItemDeserializer and ItemSerializer by calling our custom version of their
         * {@see ItemDeserializer::map()} and {@see ItemSerializer::map()} methods without prohibiting the 
         * overwriting of existing entries
         */
        (function(string $id, Closure $deserializer) : void {
            $this->deserializers[$id] = $deserializer;
        })->call(
            GlobalItemDataHandlers::getDeserializer(), 
            ItemTypeNames::HOPPER,
            fn (SavedItemData $data) => $newHopper->asItem()
        );
        (function(Block $block, Closure $serializer) : void {
            $this->blockItemSerializers[$block->getTypeId()] = $serializer;
        })->call(
            GlobalItemDataHandlers::getSerializer(),
            $newHopper,
            fn() => new SavedItemData(ItemTypeNames::HOPPER)
        );

        /**
         * Recreating the creative inventory, so that it includes our custom hopper item instance.
         */
        CreativeInventory::reset();
        CreativeInventory::getInstance();

        /**
         * Overwriting the servers crafting manager, so that all recipes using the old hopper item are replaced and
         * use the new hopper item instead.
         */
        (function() : void {
            $this->craftingManager = CraftingManagerFromDataHelper::make(Path::join(\pocketmine\BEDROCK_DATA_PATH, "recipes"));
        })->call(Server::getInstance());

        /*GlobalBlockStateHandlers::getDeserializer()->map(
            BlockTypeNames::HOPPER,
            function(BlockStateReader $in) use($newHopper) : Block {
                return (clone $newHopper)
                    ->setFacing($in->readFacingWithoutUp())
                    ->setPowered($in->readBool(BlockStateNames::TOGGLE_BIT));
            }
        );
        GlobalBlockStateHandlers::getSerializer()->map($newHopper, function(Hopper $block) : BlockStateWriter {
            return BlockStateWriter::create(BlockTypeNames::HOPPER)
                ->writeBool(BlockStateNames::TOGGLE_BIT, $block->isPowered())
                ->writeFacingWithoutUp($block->getFacing());
        });*/
    }
}