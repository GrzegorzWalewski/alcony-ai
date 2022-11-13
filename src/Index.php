<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use Discord\WebSockets\Event;
use Exception;
use Grzojda\AlconyAi\Models\Mp4ToWebm;
use FFMpeg\FFMpeg;
use Grzojda\AlconyAi\Models\Config;
use Grzojda\AlconyAi\Models\FTP;
use Grzojda\AlconyAi\Models\Util;
use Grzojda\SimpleCache\SimpleCache;
use Symfony\Component\Dotenv\Dotenv;

class Index
{
    private const CACHE_DIR = 'urls.json';

    private Mp4ToWebm $mp4ToWebm;
    private Config $config;

    public function __construct()
    {
        $simpleCache = new SimpleCache(self::CACHE_DIR);
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
            // Listen for messages.
            $discord->on(Event::MESSAGE_CREATE, function (Message $message) use ($discord) {
                try {
                    $this->mp4ToWebm->initialize($message);
                } catch (Exception $exception) {
                    $user = $discord->users->find(function (User $user) {
                        return $user->id == '163430231791632385';
                    });

                    // Send message to bot developer for debug
                    $user->sendMessage('Error: ' . $exception->getMessage() . ' in file "' . $exception->getFile() . '"(' . $exception->getLine() . ') Message was: ' . $message->content);
                }
            });
        });

        $discord->run();
    }
}