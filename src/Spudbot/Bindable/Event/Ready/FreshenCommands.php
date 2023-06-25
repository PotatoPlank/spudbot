<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Ready;

use Discord\Parts\Interactions\Command\Command;
use Discord\Repository\Interaction\GlobalCommandRepository;
use Spudbot\Bindable\Event\OnReadyExecuteBinds;
use Spudbot\Interface\IBindableEvent;

class FreshenCommands extends IBindableEvent
{
    protected string $event = 'ready';

    public function getListener(): callable
    {
        return function (OnReadyExecuteBinds $readyEvent) {
            if ($_ENV['SENTRY_ENV'] === 'production') {
                $this->discord->application->commands->freshen()->done(
                    function (GlobalCommandRepository $commandRepository) {
                        /**
                         * @var Command $command
                         */
                        foreach ($commandRepository as $command) {
                            $commandRegistered = $this->spud->commands->has(function ($boundCommand) use ($command) {
                                return $boundCommand->getName() === $command->name;
                            });
                            if (!$commandRegistered) {
                                $this->discord->getLogger()->notice("Removed command {$command->name}");
                                $this->discord->application->commands->delete($command);
                            }
                        }
                    }
                );
            }
        };
    }
}