<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Models;

use Discord\Parts\Channel\Message;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\WebM;
use Grzojda\SimpleCache\SimpleCache;

class Mp4ToWebm
{
    private const EXTENSION = 'webm';
    private const AUDIO_CODEC = 'libvorbis';
    private const VIDEO_CODEC = 'libvpx-vp9';
    private Util $util;
    private SimpleCache $cache;
    private Config $config;
    private FFMpeg $FFMpeg;
    private FTP $ftp;
    private string $serverUrl;
    private string $uploadDir;
    private string $watermark;

    public function __construct(
        Util $util,
        SimpleCache $cache,
        Config $config,
        FFMpeg $FFMpeg,
        FTP $ftp
    )
    {
        $this->util = $util;
        $this->cache = $cache;
        $this->config = $config;
        $this->FFMpeg = $FFMpeg;
        $this->ftp = $ftp;
        $this->serverUrl = $this->config->getConfigValue('SERVER_URL');
        $this->uploadDir = $this->config->getConfigValue('UPLOAD_DIR');
        $this->watermark = __DIR__ . '../Resources/watermark.gif';
    }

    public function initialize(Message $message): void
    {
        if ($this->util->checkTextContainUrl($message->content)) {
            $this->execute($message);
        }
    }

    private function execute(Message $message): void
    {
        $url = $this->util->getUrlFromText($message->content);
        $cached = $this->cache->checkKey($url);

        if ($cached !== false) {
            $videoUrl = $cached;
        } else {
            $fileName = $this->util->generateFileName($this->config->getConfigValue('prefix'), self::EXTENSION);
            $this->convert($url, $fileName);
            $this->ftp->upload($fileName);
            $videoUrl = $this->generateNewUrl($fileName);

            $this->cache->saveValue($url, $videoUrl);
        }

        $reply = $message->reply('Pliki mp4 zwykle nie ładują się na discordzie. Zmieniamy typ na .webm, za chwilę dostaniesz link, w wiadomości prywatnej. Użyj go, aby wrzucić filmik ;)');
        $message->delayedDelete(5000);
        $reply->done(function (Message $message) {
            $message->delayedDelete(5000);
        });

        $message->author->sendMessage('Link do wrzuconego przez ciebie video: ' . $videoUrl);
    }

    private function convert($url, $fileName): void
    {
        $video = $this->FFMpeg->open($url);
        $video
            ->filters()
            ->watermark($this->watermark, array(
                'position' => 'relative',
                'bottom' => 0,
                'right' => 0,
            ))
            ->watermark($this->watermark, array(
                'position' => 'relative',
                'top' => 0,
                'left' => 0,
            ));
        $video
            ->save(new WebM(self::AUDIO_CODEC, self::VIDEO_CODEC), $fileName);
    }

    private function generateNewUrl($fileName): string
    {
        return $this->serverUrl . $this->uploadDir . $fileName;
    }

}