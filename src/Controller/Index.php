<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;
use Discord\WebSockets\Event;
use Exception;
use FFMpeg\FFMpeg;
use Grzojda\AlconyAi\Commands\Functions;
use Grzojda\AlconyAi\Commands\Regulations;
use Grzojda\AlconyAi\Models\Config;
use Grzojda\AlconyAi\Models\FTP;
use Grzojda\AlconyAi\Models\Mp4ToWebm;
use Grzojda\AlconyAi\Models\Util;
use Grzojda\SimpleCache\SimpleCache;
use Symfony\Component\Dotenv\Dotenv;

class Index
{
    private const CACHE_NAME = 'urls.json';

    private Mp4ToWebm $mp4ToWebm;
    private Config $config;
    private array $commands;

    public function __construct()
    {
        $simpleCache = new SimpleCache(self::CACHE_NAME);
        $dotEnv = new Dotenv();
        $this->config = new Config($dotEnv);
        $FFMpeg = FFMpeg::create();
        $ftp = new FTP($this->config);
        $util = new Util();
        $this->mp4ToWebm = new Mp4ToWebm($util, $simpleCache, $this->config, $FFMpeg, $ftp);
    }

    public function init(): void
    {
        $discord = new Discord([
            'token' => $this->config->getConfigValue('DISCORD_BOT_TOKEN'),
        ]);

        $discord->on('ready', function (Discord $discord) {
            echo "Bot is ready!", PHP_EOL;

            $this->registerCommands();
            $this->createCommands($discord);
            $this->registerListeners($discord);
            $this->listenCommands($discord);
        });

        $discord->run();
    }

    private function registerCommands(): void
    {
        $this->commands = [
            new Regulations(),
            new Functions()
        ];
    }

    private function createCommands($discord)
    {
        foreach ($this->commands as $command) {
            $command = new Command($discord, ['name' => $command->getName(), 'description' => $command->getDescription()]);
        }

        $discord->application->commands->save($command);
    }

    private function registerListeners($discord): void
    {
        $discord->on(Event::MESSAGE_CREATE, function (Message $message) use ($discord) {
            try {
                $this->mp4ToWebm->initialize($message);
            } catch (Exception $exception) {
                $user = $discord->users->find(function (User $user) {
                    return $user->id == '163430231791632385';
                });

                // Send message to bot developer for debug
                $user->sendMessage('Error: ' . $exception->getMessage() . ' in file "' . $exception->getFile() . '"(' . $exception->getLine() . ')');
            }
        });
    }

    private function listenCommands($discord)
    {
        foreach ($this->commands as $command) {
            $discord->listenCommand($command->getName(), function (Interaction $interaction) use ($command) {
                $command->execute($interaction);
            });
        }
    }
}