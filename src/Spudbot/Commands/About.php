<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Bot\ApplicationVersion;

class About extends AbstractCommandSubscriber
{
    public function getCommandName(): string
    {
        return 'about';
    }

    public function getCommandDescription(): string
    {
        return 'About this bot.';
    }

    public function update(Interaction $interaction = null): void
    {
        if (!$interaction) {
            $this->throwMissingArgs(['interaction']);
        }

        $message = $this->spud->twig->render('about.twig', [
            'version' => ApplicationVersion::get(),
            'applicationOwnerId' => $this->spud->discord->application->owner->id,
        ]);

        $this->spud->interact()
            ->setTitle('About')
            ->setDescription($message)
            ->respondTo($interaction);
    }
}
