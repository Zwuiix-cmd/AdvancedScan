<?php

namespace Zwuiix\AdvancedScan\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\BooleanArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\FloatArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\IntegerArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\args\StringEnumArgument;
use Zwuiix\AdvancedScan\lib\CortexPE\Commando\BaseSubCommand;
use Zwuiix\AdvancedScan\lib\jojoe77777\FormAPI\CustomForm;

abstract class ScanSubCommand extends BaseSubCommand
{
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $this->onNormalRun($sender, $aliasUsed, $args);
        } else {
            $this->onBasicRun($sender, $args);
        }
    }

    public function onBasicRun(CommandSender $sender, array $args): void
    {
    }

    public function onNormalRun(Player $sender, string $aliasUsed, array $args): void
    {
    }

    public function onFormRun(Player $sender, string $aliasUsed, array $args): void
    {
        $commandArguments = [];
        $enums = [];
        foreach ($this->getArgumentList() as $position => $arguments) {
            foreach ($arguments as $argument) {
                $commandArguments[$position] = $argument;
                if ($argument instanceof StringEnumArgument) $enums[$position] = $argument->getEnumValues();
            }
        }

        $form = new CustomForm(function (Player $player, ?array $data) use ($enums): void {
            if ($data !== null) {
                $args = [];
                foreach ($this->getArgumentList() as $position => $arguments) {
                    $position=$position+1;
                    if (!isset($data[$position])) continue;
                    foreach ($arguments as $argument) {
                        $wrappedArgument = $argument;
                        if ($wrappedArgument instanceof StringEnumArgument && !$wrappedArgument instanceof BooleanArgument) {
                            $args[$argument->getName()] = $enums[$position][$data[$position]];
                        } elseif ($wrappedArgument instanceof IntegerArgument) {
                            $args[$argument->getName()] = (int)$data[$position];
                        } elseif ($wrappedArgument instanceof FloatArgument) {
                            $args[$argument->getName()] = (float)$data[$position];
                        } else {
                            $args[$argument->getName()] = $data[$position];
                        }
                    }
                }
                $this->onNormalRun($player, $this->getName(), $args);
            }
        });
        $form->setTitle("Scan (" . $this->getName() . ")");
        $form->addLabel($this->getDescription());
        foreach ($commandArguments as $argument) {
            if ($argument instanceof BooleanArgument) {
                $form->addToggle(ucfirst($argument->getName()), $args[$argument->getName()] ?? null);
            } else {
                $form->addInput(ucfirst($argument->getName()), "", $args[$argument->getName()] ?? null);
            }
        }
        $sender->sendForm($form);
    }

}