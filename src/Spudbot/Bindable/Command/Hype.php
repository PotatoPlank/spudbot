<?php

namespace Spudbot\Bindable\Command;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;

class Hype extends IBindableCommand
{
    protected string $name = 'hype';
    protected string $description = 'Hype from the ultimate hype man.';
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
}