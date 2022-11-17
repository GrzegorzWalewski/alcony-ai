<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Interfaces;

use Discord\Discord;
use Discord\Parts\Interactions\Interaction;

interface CommandInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function getOptions(Discord $discord): array;
    public function execute(Interaction $interaction, Discord $discord): void;
}