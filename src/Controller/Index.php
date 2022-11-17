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
    private Commands $commands;
    private Listeners $listeners;

    public function __construct()
    {
        $simpleCache = new SimpleCache(self::CACHE_NAME);
        $dotEnv = new Dotenv();
        $this->config = new Config($dotEnv);
        $util = new Util();

        $this->commands = new Commands($simpleCache, $this->config, $util);
        $this->listeners = new Listeners($simpleCache, $this->config, $util);
    }

    public function init(): void
    {
        $discord = new Discord([
            'token' => $this->config->getConfigValue('DISCORD_BOT_TOKEN'),
        ]);

        $discord->on('ready', function (Discord $discord) {
            echo "Bot is ready!", PHP_EOL;

            $this->commands->init($discord);
            $this->listeners->init($discord);
        });

        $discord->run();
    }




}