<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;

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

        $builder = $this->spud->getSimpleResponseBuilder();
        if ($this->spud->discord->application->owner->id !== $interaction->user->id) {
            $builder->setTitle('Invalid Permissions for Purge Commands');
            $builder->setDescription('You don\'t have the necessary permissions to run this command.');

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
            return;
        }

        $builder->setTitle('Purge Commands');
        $builder->setDescription('Commands are now being purged. The bot will restart when it\'s complete.');
        $interaction->respondWithMessage($builder->getEmbeddedMessage())->done(function () {
            $this->spud->discord->application->commands->freshen()->then(function ($commands) {
                foreach ($commands as $i => $command) {
                    $this->spud->discord->getLogger()->alert("Purging the command: {$command->name}");
                    $exit = $i === (count($commands) - 1) ? function () {
                        $this->spud->kill();
                    } : null;
                    $this->spud->discord->application->commands->delete($command)->done($exit);
                }
            });
        });
    }
}
