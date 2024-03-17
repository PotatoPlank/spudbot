<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;

class Restart extends AbstractCommandSubscriber
{

    public function getCommandName(): string
    {
        return 'restart';
    }

    public function getCommandDescription(): string
    {
        return 'Restart Spud to fix issues or load new functionality.';
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
            ->setTitle('Restart')
            ->setDescription('The bot will now restart.')
            ->respondTo($interaction, true)
            ->done(function () {
                $this->spud->terminate();
            });
    }
}
