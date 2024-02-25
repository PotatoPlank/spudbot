<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;

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

        $builder = $this->spud->getSimpleResponseBuilder();

        if ($this->spud->discord->application->owner->id !== $interaction->user->id) {
            $builder->setTitle('Invalid Permissions for Restart');
            $builder->setDescription('You don\'t have the necessary permissions to run this command.');

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
            return;
        }

        $builder->setTitle('Restart');
        $builder->setDescription('The bot will now restart.');

        $interaction->respondWithMessage($builder->getEmbeddedMessage(), true)->done(function () {
            $this->spud->kill();
        });
    }
}
