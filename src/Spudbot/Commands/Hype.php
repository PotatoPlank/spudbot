<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;

class Hype extends AbstractCommandSubscriber
{
    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $builder = $this->spud->getSimpleResponseBuilder();
        $hypeGifs = [
            [
                'url' => 'https://media.tenor.com/1RVeTBOtmi4AAAAC/lil-jon-yeah.gif',
                'message' => 'YEAH!!',
            ],
            [
                'url' => 'https://media.tenor.com/GQFMbuWapkcAAAAC/lil-jon-ok.gif',
                'message' => 'OKAAY!!',
            ],
        ];

        $selectedIndex = array_rand($hypeGifs);
        $selectedGif = $hypeGifs[$selectedIndex];
        $options['image']['url'] = $selectedGif['url'];

        $builder->setTitle($selectedGif['message']);
        $builder->setOptions($options);

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }

    public function getCommandName(): string
    {
        return 'hype';
    }

    public function getCommandDescription(): string
    {
        return 'Hype from the ultimate hype man.';
    }
}
