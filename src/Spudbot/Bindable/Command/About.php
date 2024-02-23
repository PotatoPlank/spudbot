<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Command;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Bot\Spud;
use Spudbot\Interface\IBindableCommand;

class About extends IBindableCommand
{
    protected string $name = 'about';
    protected string $description = 'About this bot.';

    public function getListener(): callable
    {
        return function (Interaction $interaction) {
            $builder = $this->spud->getSimpleResponseBuilder();
            $builder->setTitle('About');

            $context = [
                'version' => Spud::getVersionString(),
                'applicationOwnerId' => $this->discord->application->owner->id,
            ];

            $message = $this->spud->twig->render('about.twig', $context);
            $builder->setDescription($message);

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        };
    }
}
