<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Commands;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Grzojda\AlconyAi\Interfaces\CommandInterface;

class Regulations implements CommandInterface
{
    private const NAME = 'regulamin';
    private const DESCRIPTION = 'Przeczytaj regulamin Alconów.';

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
        return [];
    }

    public function execute(Interaction $interaction, Discord $discord): void
    {
        $content = '
        REGULAMIN:
        1. Treści NSFW trafiają na kanał <#423408927934513154> . Obejmuje to wiadomości tekstowe, obrazy i linki zawierające nagość, seks, dotkliwą przemoc i inne poważnie niepokojące treści.
        2. Szanujemy siebie wzajemnie
        3. Na kanałach głosowych utrzymujemy porządek - nie jemy, nie krzyczymy itp.
        4. Komendy do botów uruchamiamy jedynie na kanale do tego przeznaczonym tj. <#602244181095612466>
        5. Zakaz spamu i autopromocji (zaproszeń na serwery, reklam itp.) bez zezwolenia od administratora. Dotyczy to również prywatnych wiadomości do innych członków.
        6. Zachęcamy do dodawania do nicku imienia - ułatwia to znacznie komunikacje
        7. Korzystanie z kanału jest jednoznaczne z akceptacją regulaminu';

        $interaction->respondWithMessage(MessageBuilder::new()->setContent($content), true);
    }
}