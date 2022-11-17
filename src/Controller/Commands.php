<?php

namespace Grzojda\AlconyAi\Controller;

use Discord\Discord;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Interactions\Command\Choice;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Grzojda\AlconyAi\Commands\Functions;
use Grzojda\AlconyAi\Commands\Music\Play;
use Grzojda\AlconyAi\Commands\Regulations;
use Grzojda\AlconyAi\Models\Config;
use Grzojda\AlconyAi\Models\Util;
use Grzojda\SimpleCache\SimpleCache;

class Commands
{
    private array $commands;
    private SimpleCache $simpleCache;
    private Config $config;
    private Util $util;

    public function __construct(SimpleCache $simpleCache,Config $config, Util $util)
    {
        $this->simpleCache = $simpleCache;
        $this->config = $config;
        $this->util = $util;
    }

    public function init(Discord $discord)
    {
        $this->registerCommands();
        $this->createCommands($discord);
        $this->listenCommands($discord);
    }

    private function registerCommands(): void
    {
        $this->commands = [
            new Regulations(),
            new Functions(),
            new Play()
        ];
    }

    private function createCommands(Discord $discord)
    {
        foreach ($this->commands as $command) {
            $guild = $discord->guilds->find(function (Guild $guild) {
                if ($guild->id === '1035625636313698344')
                {
                    return $guild;
                }
            });

            $createdCommand = new Command($discord, ['name' => $command->getName(), 'description' => $command->getDescription(), 'options' => $command->getOptions($discord)]);
            $guild->commands->save($createdCommand);
//            $discord->application->commands->save($createdCommand);
        }
    }

    private function listenCommands(Discord $discord)
    {
        foreach ($this->commands as $command) {
            $discord->listenCommand($command->getName(), function (Interaction $interaction) use ($command, $discord) {
                $command->execute($interaction, $discord);
            });
        }
    }
}