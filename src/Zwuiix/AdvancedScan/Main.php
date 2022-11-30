<?php

namespace Zwuiix\AdvancedScan;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Zwuiix\AdvancedScan\commands\ScanCommand;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\exception\HookAlreadyRegistered;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\PacketHooker;

class Main extends PluginBase
{
    use SingletonTrait;

    /**
     * @throws HookAlreadyRegistered
     */
    protected function onEnable(): void
    {
        if(!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        $this->getServer()->getCommandMap()->register("scan", new ScanCommand($this));
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }
}