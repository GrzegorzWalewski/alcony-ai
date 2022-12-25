<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Interfaces;

use Discord\Parts\Interactions\Interaction;

interface CommandInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function execute(Interaction $interaction): void;
}