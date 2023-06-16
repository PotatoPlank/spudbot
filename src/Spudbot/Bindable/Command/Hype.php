<?php

namespace Spudbot\Bindable\Command;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Command as CommandPart;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class Hype extends BindableCommand
{
    public function getListener(): callable
    {
        return function (Interaction $interaction){
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
        };
    }

    public function getCommand(): Command
    {
        $attributes = [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];

        return new Command($this->discord, $attributes);
    }

    public function getName(): string
    {
        return 'hype';
    }

    public function getDescription(): string
    {
        return 'Hype from the ultimate hype man.';
    }
}