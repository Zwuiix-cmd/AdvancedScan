<?php

namespace Zwuiix\AdvancedScan\commands\sub;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\EnderChest;
use pocketmine\block\tile\Furnace;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\leveldb\LevelDB;
use pocketmine\world\format\io\WorldProviderManager;
use RuntimeException;
use Webmozart\PathUtil\Path;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\IntegerArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\RawStringArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\exception\ArgumentOrderException;
use Zwuiix\AdvancedScan\Main;
use Zwuiix\AdvancedScan\tasks\ScanInventoryTask;
use Zwuiix\AdvancedScan\utils\PathScanner;
use Zwuiix\AdvancedScan\commands\ScanSubCommand;

class ScanSubEnderChest extends ScanSubCommand
{
    public function __construct()
    {
        parent::__construct("enderchest", "Scan enderchest", []);
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new IntegerArgument("id"));
        $this->registerArgument(1, new IntegerArgument("meta"));
        $this->registerArgument(2, new IntegerArgument("min"));
        $this->registerArgument(3, new IntegerArgument("tick", true));
        $this->setPermission("scan.enderchest");
    }

    /**
     * @param Player $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onNormalRun(Player $sender, string $aliasUsed, array $args): void
    {
        $id=$args["id"];
        $meta=$args["meta"];
        $min=$args["min"];

        $sender->sendMessage(TextFormat::GREEN."[SCAN] : Scan in progress...");
        $players=[];
        foreach (scandir(Server::getInstance()->getDataPath()."players/",0) as $item => $value){
            if(str_contains($value, ".dat")){
                $name=str_replace(".dat", "", $value);
                $pl=Server::getInstance()->getPlayerByPrefix($name);
                if(!$pl instanceof Player){
                    $player=Server::getInstance()->getOfflinePlayer($name);
                    $players[]=$player->getName();
                }
            }
        }

        $task = new ScanInventoryTask($players, count($players), $id, $meta, $min, $sender, ScanInventoryTask::ENDERCHEST);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, $args["tick"] ?? 2);
    }
}