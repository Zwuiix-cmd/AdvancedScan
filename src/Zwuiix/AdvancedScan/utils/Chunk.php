<?php

namespace Zwuiix\AdvancedScan\utils;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\OfflinePlayer;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class Chunk
{
    use SingletonTrait;

    public function scanInventory(): void
    {
        $config=new Config(Server::getInstance()->getDataPath()."plugin_data/ASCoreKitmap/datas/economy.json", Config::JSON);
        foreach ($config->getAll() as $value => $i){
            $pl=Server::getInstance()->getPlayerByPrefix($value);
            if(!$pl instanceof Player){
                $player=Server::getInstance()->getOfflinePlayer($value);
                $inventory=Chunk::getInstance()->readInventory($player);
                foreach ($inventory as $item){
                    if($item instanceof Item){
                        if($item->getId() == ItemIds::TRIPWIRE_HOOK && $item->getCount() > 32){
                            Server::getInstance()->getLogger()->info("{$player->getName()} : x{$item->getCount()} ({$item->getCustomName()})");
                        }
                    }
                }
            }
        }
    }

    public function scanEnderInventory(): void
    {
        $config=new Config(Server::getInstance()->getDataPath()."plugin_data/ASCoreKitmap/datas/economy.json", Config::JSON);
        foreach ($config->getAll() as $value => $i){
            $pl=Server::getInstance()->getPlayerByPrefix($value);
            if(!$pl instanceof Player){
                $player=Server::getInstance()->getOfflinePlayer($value);
                $inventory=Chunk::getInstance()->readEnderInventory($player);
                foreach ($inventory as $item){
                    if($item instanceof Item){
                        if($item->getId() == ItemIds::TRIPWIRE_HOOK && $item->getCount() > 32){
                            Server::getInstance()->getLogger()->info("{$player->getName()} : x{$item->getCount()} ({$item->getCustomName()})");
                        }
                    }
                }
            }
        }
    }

    public function readEnderInventory(OfflinePlayer $player) : array
    {
        $user=Server::getInstance()->getOfflinePlayerData($player->getName());
        $enderChestInventoryTag = $user->getListTag("EnderChestInventory");
        if($enderChestInventoryTag === null){
            return [];
        }

        $ender_inventory = [];
        /** @var CompoundTag $item */
        foreach($enderChestInventoryTag->getIterator() as $i => $item){
            $ender_inventory[$item->getByte("Slot")] = Item::nbtDeserialize($item);
        }
        return $ender_inventory;
    }

    public function writeEnderInventory(OfflinePlayer $player, array $inventory) : self{
        $user=Server::getInstance()->getOfflinePlayerData($player->getName());
        $tag = new ListTag([], NBT::TAG_Compound);
        foreach($inventory as $slot => $item){
            $tag->push($item->nbtSerialize($slot));
        }
        $user->setTag("EnderChestInventory", $tag);
        return $this;
    }

    /**
     * @param OfflinePlayer $player
     * @return array
     */
    public function readInventory(OfflinePlayer $player) : array
    {
        $user=Server::getInstance()->getOfflinePlayerData($player->getName());
        $InventoryTag = $user->getListTag("Inventory");
        if($InventoryTag === null){
            return [];
        }

        $inventory = [];
        /** @var CompoundTag $item */
        foreach($InventoryTag->getIterator() as $i => $item){
            $inventory[$item->getByte("Slot")] = Item::nbtDeserialize($item);
        }
        return $inventory;
    }

    /**
     * @param array<int, Item> $inventory
     */
    public function writeInventory(OfflinePlayer $player, array $inventory) : void
    {
        $user=Server::getInstance()->getOfflinePlayerData($player->getName());
        $serialized_inventory = [];
        foreach($inventory as $slot => $item){
            $serialized_inventory[] = $item->nbtSerialize($slot + 9);
        }
        $user->setTag("Inventory", new ListTag($serialized_inventory, NBT::TAG_Compound));
    }
}