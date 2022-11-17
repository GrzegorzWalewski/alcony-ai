<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Commands;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Grzojda\AlconyAi\Interfaces\CommandInterface;

class Functions implements CommandInterface
{
    private const NAME = 'funkcje';
    private const DESCRIPTION = 'Sprawdź listę wszystkich funkcji naszego bota.';
    public function getName(): string
    {
        return self::NAME;
    }

    public function getDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function execute(Interaction $interaction, Discord $discord): void
    {
        $content = '
        Dostępne funkcje:
        1. Przekonwertowywanie linków MP4, do linków WEBM (automatycznie)
        2. Informacje:
            - /regulamin';

        $interaction->respondWithMessage(MessageBuilder::new()->setContent($content), true);
    }

    public function getOptions(Discord $discord): array
    {
        return [];
    }
}