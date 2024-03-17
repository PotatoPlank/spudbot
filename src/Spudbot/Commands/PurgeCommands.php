<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;

class PurgeCommands extends AbstractCommandSubscriber
{
    public function getCommandName(): string
    {
        return 'purge_commands';
    }

    public function getCommandDescription(): string
    {
        return 'Purges bound commands within the bot.';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }

        if ($this->spud->discord->application->owner->id !== $interaction->user->id) {
            $this->spud->interact()
                ->error('You don\'t have the necessary permissions to run this command.')
                ->respondTo($interaction);
            return;
        }

        $this->spud->interact()
            ->setTitle('Purge Commands')
            ->setDescription('Commands are now being purged. The bot will restart when it\'s complete.')
            ->respondTo($interaction)
            ->done(function () {
                $this->spud->discord->application->commands->freshen()->then(function ($commands) {
                    foreach ($commands as $i => $command) {
                        $this->spud->discord->getLogger()->alert("Purging the command: {$command->name}");
                        $exit = $i === (count($commands) - 1) ? function () {
                            $this->spud->terminate();
                        } : null;
                        $this->spud->discord->application->commands->delete($command)->done($exit);
                    }
                });
            });
    }
}
