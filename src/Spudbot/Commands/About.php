<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Bot\ApplicationVersion;
use Spudbot\Interface\AbstractCommandSubscriber;

class About extends AbstractCommandSubscriber
{
    public function getCommandName(): string
    {
        return 'about_test';
    }

    public function getCommandDescription(): string
    {
        return 'About this bot.';
    }

    public function update(Interaction $interaction = null): void
    {
        if ($interaction === null) {
            $this->throwMissingArgs(['interaction']);
        }
        $builder = $this->spud->getSimpleResponseBuilder();
        $builder->setTitle('About');

        $context = [
            'version' => ApplicationVersion::get(),
            'applicationOwnerId' => $this->spud->discord->application->owner->id,
        ];

        $message = $this->spud->twig->render('about.twig', $context);
        $builder->setDescription($message);

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}
