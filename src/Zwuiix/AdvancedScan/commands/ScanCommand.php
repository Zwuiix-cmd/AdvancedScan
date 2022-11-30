<?php

namespace Zwuiix\AdvancedScan\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use Zwuiix\AdvancedScan\commands\sub\ScanSubEnderChest;
use Zwuiix\AdvancedScan\commands\sub\ScanSubInventory;
use Zwuiix\AdvancedScan\commands\sub\ScanSubMap;
use Zwuiix\AdvancedScan\commands\sub\ScanSubPlugin;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\BaseCommand;

class ScanCommand extends BaseCommand
{
    public function __construct(protected Plugin $plugin)
    {
        parent::__construct($plugin, "scan", "Scan global server");
    }

    protected function prepare(): void
    {
        // SOON = $this->registerSubCommand(new ScanSubPlugin());
        $this->registerSubCommand(new ScanSubMap());
        $this->registerSubCommand(new ScanSubInventory());
        $this->registerSubCommand(new ScanSubEnderChest());
        $this->setPermission("advancedscan");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $this->sendUsage();
    }
}