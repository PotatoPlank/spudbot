<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Carbon\Carbon;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Helpers\Collection;

class Boot extends AbstractEventSubscriber
{
    public function update(): void
    {
        $this->spud->commandObserver->all()->forEach(function (Collection $listeners, $commandName) {
            $listeners->forEach(function ($command) use ($commandName) {
                $this->spud->discord->application->commands->save($command->getCommand());
                $this->spud->discord->listenCommand($commandName, function (...$args) use ($command, $commandName) {
                    $this->spud->discord->getLogger()->info("$commandName command called.");
                    if ($command->canRun(...$args)) {
                        $command->update(...$args);
                    }
                });
            });
        });

        $this->spud->eventObserver->destroy(Events::READY->value);
        $this->spud->eventObserver->all()->forEach(function ($listeners, $eventName) {
            $this->spud->discord->on($eventName, function (...$args) use ($eventName) {
                $this->spud->eventObserver->emit($eventName, ...$args);
                $this->spud->discord->getLogger()
                    ->info("Subscribers to '$eventName' called.");
            });
            $this->spud->discord->getLogger()
                ->info("Subscribed to '$eventName'.");
        });

        $description = 'GitHub: https://github.com/PotatoPlank/spudbot' . PHP_EOL . PHP_EOL;
        $description .= 'Build: ' . ApplicationVersion::get() . PHP_EOL;
        $description .= 'Started at: ' . Carbon::now('America/New_York')->toIso8601String();

        $this->spud->discord->updateCurrentApplication([
            'description' => $description,
        ])->then(function () {
            echo 'Updated description.' . PHP_EOL;
        });
    }

    public function getEventName(): string
    {
        return Events::READY->value;
    }
}
