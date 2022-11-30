<?php

namespace Zwuiix\AdvancedScan\tasks;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Furnace;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ScanMapTask extends Task
{
    protected bool $found = false;
    protected int $time = 0;

    /**
     * @param array $chunks
     * @param int $timeMax
     * @param string $world
     * @param int $id
     * @param int $meta
     * @param int $min
     * @param Player $sender
     */
    public function __construct(
        protected array $chunks,
        protected int $timeMax,
        protected string $world,
        protected int $id,
        protected int $meta,
        protected int $min,
        protected Player $sender,
    )
    {
    }

    public function onRun(): void
    {
        $wd=Server::getInstance()->getWorldManager()->getWorldByName($this->world);
        if($this->time <= $this->timeMax){
            $time = round($this->time * 100 / $this->timeMax);
            $this->sender->sendTip(TextFormat::GREEN."Scan performed at {$time}%%%");
            foreach ($this->chunks as $chunks => $allChunk){
                $wd->loadChunk($allChunk["ChunkX"], $allChunk["ChunkZ"]);
                $chunk = $wd->getChunk($allChunk["ChunkX"], $allChunk["ChunkZ"]);
                foreach ($chunk->getTiles() as $tile){
                    $position=$tile->getPosition();
                    if($tile instanceof Chest or $tile instanceof Furnace){
                        foreach ($tile->getInventory()->getContents() as $content){
                            if($content->getId() === $this->id && $content->getMeta() === $this->meta && $content->getCount() >= $this->min){
                                $this->sender->sendMessage(TextFormat::GREEN."Found §f=> §e{$content->getName()}({$content->getCustomName()}) §7x{$content->getCount()} in {$position->getX()}, {$position->getY()}, {$position->getZ()} ({$position->getWorld()->getFolderName()})");
                                $this->found=true;
                            }
                        }
                    }
                }
                unset($this->chunks[$chunks]);
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