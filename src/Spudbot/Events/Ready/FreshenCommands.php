<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Ready;

use Discord\Parts\Interactions\Command\Command;
use Discord\Repository\Interaction\GlobalCommandRepository;
use Spudbot\Bot\Events;
use Spudbot\Interface\AbstractEventSubscriber;

class FreshenCommands extends AbstractEventSubscriber
{
    protected string $event = 'ready';

    public function getEventName(): string
    {
        return Events::READY->value;
    }

    public function update(): void
    {
        $this->spud->discord->application->commands->freshen()
            ->done(function (GlobalCommandRepository $commandRepository) {
                /**
                 * @var Command $command
                 */
                foreach ($commandRepository as $command) {
                    $commandRegistered = $this->spud->commandObserver->hasCommand($command->name);
                    if ($commandRegistered) {
                        continue;
                    }
                    $this->spud->discord->getLogger()
                        ->notice("Removed command {$command->name}");
                    $this->spud->discord->application->commands
                        ->delete($command);
                }
            });
    }

    public function canRun(): bool
    {
        return $_ENV['SENTRY_ENV'] === 'production';
    }
}
