<?php

namespace Grzojda\AlconyAi\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use Discord\WebSockets\Event;
use Exception;
use FFMpeg\FFMpeg;
use Grzojda\AlconyAi\Models\Config;
use Grzojda\AlconyAi\Models\FTP;
use Grzojda\AlconyAi\Models\Mp4ToWebm;
use Grzojda\AlconyAi\Models\Util;
use Grzojda\SimpleCache\SimpleCache;

class Listeners
{
    private SimpleCache $simpleCache;
    private Config $config;
    private Util $util;
    private Mp4ToWebm $mp4ToWebm;

    public function __construct(SimpleCache $simpleCache, Config $config, Util $util)
    {
        $this->simpleCache = $simpleCache;
        $this->config = $config;
        $this->util = $util;

        $FFMpeg = FFMpeg::create();
        $ftp = new FTP($this->config);
        $this->mp4ToWebm = new Mp4ToWebm($util, $simpleCache, $this->config, $FFMpeg, $ftp);
    }

    public function init(Discord $discord)
    {
        $this->registerListeners($discord);
    }

    private function registerListeners(Discord $discord): void
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
}