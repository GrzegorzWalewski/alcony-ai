<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Commands\Music;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Request\Option as RequestOption;
use Discord\Parts\Interactions\Interaction;
use Discord\Voice\VoiceClient;
use Grzojda\AlconyAi\Interfaces\CommandInterface;
use Grzojda\AlconyAi\Models\YoutubeDlExtender;
use Grzojda\SimpleCache\SimpleCache;
use React\Stream\ReadableResourceStream;
use YoutubeDl\Options;

class Play implements CommandInterface
{
    private const NAME = 'youtube';
    private const DESCRIPTION = 'Zagraj z yt';
    private SimpleCache $simpleCache;

    public function __construct()
    {
        $this->simpleCache = new SimpleCache('playlist.json');
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function getOptions(Discord $discord): array
    {
        $option = new Option($discord, [
            'type' => 3,
            'name' => 'url',
            'description' => 'Provide url to play from',
            'required' => true,
            'choices' => [],
            'options' => [],
            'channel_types' => null,
            'min_value' => null,
            'max_value' => null,
            'min_length' => 4,
            'max_length' => 100,
            'autocomplete' => false
        ]);

        return [$option];
    }

    public function execute(Interaction $interaction, Discord $discord): void
    {
        $channel = $interaction->member->getVoiceChannel();
        $guildId = $interaction->member->guild_id;
        $voiceClient = $discord->getVoiceClient($guildId);
        $queue = $this->simpleCache->getArray() ?? [];
        $urlOption = $interaction->data->options->find(function (RequestOption $option) {
            if ($option->name === 'url') {
                return $option;
            }
            return '';
        });

        if ($channel === null) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent('Aby puścić muzykę musisz być na kanale głosowym!'), true);
        } elseif ($voiceClient !== null && $voiceClient->getChannel()->id !== $channel->id) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent('Bot działa już na innym kanale głosowym :/'), true);
        } elseif ($voiceClient->isSpeaking()) {
            $lastKey = array_key_last($queue);

            if ($lastKey !== null) {
                $this->simpleCache->saveValue($lastKey + 1, $urlOption->value);
            }

            $interaction->respondWithMessage(MessageBuilder::new()->setContent('Piosenka została dodana do kolejki'));
        } else {
            $this->playSong($discord, $interaction, 'url');
            $interaction->respondWithMessage(MessageBuilder::new()->setContent('Za chwilę zagramy...'));
        }

//$audioUrl = 'http://noisefm.ru:8000/play?icy=http'; granie na czekanie
    }

    public function playSong(Discord $discord, Interaction $interaction, string $url)
    {
        $youtube = new YoutubeDlExtender();
        $youtube->setPythonPath('/usr/bin/python3');
        $url = $youtube->getAudioUrl(
            Options::create()
                ->url($url)
        );

        $audioUrl = 'Resources/Music/file.wav';
// download the rest of music
        $cmd = 'ffmpeg -ss 00:00:30.000 -accurate_seek -i "'.$url.'" file2.wav > /dev/null &';
        exec($cmd);

        $discord->joinVoiceChannel($channel)->then(function (VoiceClient $client) use ($audioUrl, $url) {
            // download first 30sec
            $cmd = 'ffmpeg -ss 00:00:00.000 -accurate_seek -i "'.$url.'" -t 00:00:30.000 file.wav';
            exec($cmd);
            $stream = new ReadableResourceStream(fopen($audioUrl,'r'));

            $client->playRawStream($stream)->then(function () use ($client) {
                $stream = new ReadableResourceStream(fopen('file2.wav','r'));
                return $client->playRawStream($stream);
            }, function ($e) {
                echo "failed to play song:\n";
            });
        }, function ($e) {
            echo "failed to join voice channel:\n";
        });
    }
}