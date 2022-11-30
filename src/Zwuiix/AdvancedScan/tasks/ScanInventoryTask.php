<?php

namespace Zwuiix\AdvancedScan\tasks;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Furnace;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Zwuiix\AdvancedScan\utils\Chunk;

class ScanInventoryTask extends Task
{
    protected bool $found = false;
    public const INVENTORY = 0;
    public const ENDERCHEST = 1;
    protected int $time = 0;

    /**
     * @param array $players
     * @param int $timeMax
     * @param int $id
     * @param int $meta
     * @param int $min
     * @param Player $sender
     * @param int $type
     */
    public function __construct(
        protected array $players,
        protected int $timeMax,
        protected int $id,
        protected int $meta,
        protected int $min,
        protected Player $sender,
        protected int $type,
    )
    {
    }

    public function onRun(): void
    {
        if($this->time <= $this->timeMax){
            $time = round($this->time * 100 / $this->timeMax);
            $this->sender->sendTip(TextFormat::GREEN."Scan performed at {$time}%%%");
            foreach ($this->players as $int => $name){
                $player=Server::getInstance()->getOfflinePlayer($name);

                $inventory = match ($this->type) {
                    1 => Chunk::getInstance()->readEnderInventory($player),
                    default => Chunk::getInstance()->readInventory($player),
                };

                foreach ($inventory as $item){
                    if($item instanceof Item){
                        if($item->getId() === $this->id && $item->getMeta() === $this->meta && $item->getCount() >= $this->min){
                            $this->sender->sendMessage(TextFormat::GREEN."Found §f=> §e{$item->getName()}({$item->getCustomName()}) §7x{$item->getCount()} in {$player->getName()}");
                            $this->found=true;
                        }
                    }
                }
                unset($this->players[$int]);
                $this->time++;
                return;
            }
        }

        if($this->time >= $this->timeMax){
            if(!$this->found){
                $this->sender->sendMessage(TextFormat::RED . "[SCAN] : Sorry, the scan failed, we could not find anything!");
            }else{
                $this->sender->sendMessage(TextFormat::GREEN . "[SCAN] : Success!");
            }
            $this->getHandler()->cancel();
            return;
        }
    }
}