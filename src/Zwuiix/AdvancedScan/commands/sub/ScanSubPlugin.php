<?php

namespace Zwuiix\AdvancedScan\commands\sub;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\RawStringArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\exception\ArgumentOrderException;
use Zwuiix\AdvancedScan\Main;
use Zwuiix\AdvancedScan\utils\PathScanner;
use Zwuiix\AdvancedScan\commands\ScanSubCommand;

class ScanSubPlugin extends ScanSubCommand
{
    private string $realDirName = "Unknown";

    public function __construct()
    {
        parent::__construct("plugin", "Scan plugin", []);
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("name"));
        $this->setPermission("scan.plugin");
    }

    /**
     * @param Player $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onNormalRun(Player $sender, string $aliasUsed, array $args): void
    {
        $plugin=Server::getInstance()->getPluginManager()->getPlugin($args["name"]);
        if(!$plugin instanceof Plugin){
            $sender->sendMessage(TextFormat::YELLOW."[SCAN] : {$args["name"]} not loaded!");
            return;
        }
        if(!$plugin->isEnabled()){
            $sender->sendMessage(TextFormat::YELLOW."[SCAN] : {$plugin->getName()} not loaded!");
            return;
        }
        $sender->sendMessage(TextFormat::GREEN."[SCAN] : Scan in progress...");
        $sender->sendMessage(TextFormat::GREEN."Name: §e{$plugin->getName()}");
        $sender->sendMessage(TextFormat::GREEN."Version: §e{$plugin->getDescription()->getVersion()}");
        $sender->sendMessage(TextFormat::GREEN."DataFolder: §e{$plugin->getDataFolder()}");

        $o=false;
        $path=Main::getInstance()->getServer()->getPluginPath();
        $scan1=PathScanner::scanDirectory($path, ["phar"]);
        foreach ($scan1 as $item){
            if(str_contains($item, $plugin->getName())){
                $o = true;
            }
        }
        if(!$o){
            $sender->sendMessage(TextFormat::RED."Plugin is not phar!");
            return;
        }
    }
}