<?php

namespace Zwuiix\AdvancedScan\commands\sub;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\EnderChest;
use pocketmine\block\tile\Furnace;
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
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\IntegerArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\RawStringArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\exception\ArgumentOrderException;
use Zwuiix\AdvancedScan\Main;
use Zwuiix\AdvancedScan\tasks\ScanMapTask;
use Zwuiix\AdvancedScan\utils\PathScanner;
use Zwuiix\AdvancedScan\commands\ScanSubCommand;

class ScanSubMap extends ScanSubCommand
{
    public function __construct()
    {
        parent::__construct("map", "Scan map", []);
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("world"));
        $this->registerArgument(1, new IntegerArgument("id"));
        $this->registerArgument(2, new IntegerArgument("meta"));
        $this->registerArgument(3, new IntegerArgument("min"));
        $this->registerArgument(4, new IntegerArgument("tick", true));
        $this->setPermission("scan.map");
    }

    /**
     * @param Player $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onNormalRun(Player $sender, string $aliasUsed, array $args): void
    {
        $world=$args["world"];
        $id=$args["id"];
        $meta=$args["meta"];
        $min=$args["min"];

        if(Server::getInstance()->getWorldManager()->isWorldLoaded($world)){
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($world));
            return;
        }

        $path = Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $args["world"];
        $providerManager = new WorldProviderManager();
        $worldProviderManagerEntry = null;
        foreach($providerManager->getMatchingProviders($path) as $worldProviderManagerEntry) {
            break;
        }

        if($worldProviderManagerEntry === null) {
            throw new RuntimeException("Unknown world provider");
        }

        $provider = $worldProviderManagerEntry->fromPath($path . DIRECTORY_SEPARATOR);

        if(!$provider instanceof LevelDB) {
            throw new RuntimeException("World provider " . get_class($provider) . " is not supported.");
        }

        $allChunks=[];
        foreach($provider->getAllChunks(true, Main::getInstance()->getLogger()) as $coords => $chunk){
            $chunkX=$coords[0];
            $chunkZ=$coords[1];
            $allChunks[]=["ChunkX" => $chunkX, "ChunkZ" => $chunkZ];
        }
        $provider->close();

        if(!Server::getInstance()->getWorldManager()->isWorldGenerated($args["world"])){return;}
        if(!Server::getInstance()->getWorldManager()->isWorldLoaded($args["world"])){
            Server::getInstance()->getWorldManager()->loadWorld($args["world"]);
        }
        $sender->sendMessage(TextFormat::GREEN."[SCAN] : Scan in progress...");

        $task = new ScanMapTask($allChunks, count($allChunks), $args["world"], $id, $meta, $min, $sender);
        Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, $args["tick"] ?? 2);
    }
}