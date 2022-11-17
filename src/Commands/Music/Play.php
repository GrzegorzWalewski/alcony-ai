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
use React\Stream\ReadableResourceStream;
use YoutubeDl\Options;

class Play implements CommandInterface
{
    private const NAME = 'youtube';
    private const DESCRIPTION = 'Zagraj z yt';
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
        $urlOption = $interaction->data->options->find(function (RequestOption $option) {
            if ($option->name === 'url') {
                return $option;
            }
            return '';
        });

        $youtube = new YoutubeDlExtender();
        $youtube->setPythonPath('/usr/bin/python3');
        $audioUrl = $youtube->getAudioUrl(
            Options::create()
                ->url($urlOption->value)
        );

        $discord->joinVoiceChannel($channel)->then(function (VoiceClient $client) use ($audioUrl) {
            $stream = new ReadableResourceStream(fopen($audioUrl,'r'));
            $client->playRawStream($stream)->then(function () {
                echo "done\n";
            }, function ($e) {
                echo "failed to play song:\n";
            });
        }, function ($e) {
            echo "failed to join voice channel:\n";
        });

        $interaction->respondWithMessage(MessageBuilder::new()->setContent('Now playing...'), true);
    }
}