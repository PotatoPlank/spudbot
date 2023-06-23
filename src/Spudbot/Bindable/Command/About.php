<?php

namespace Spudbot\Bindable\Command;

use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Bot\Spud;
use Spudbot\Interface\IBindableCommand;

class About extends IBindableCommand
{
    protected string $name = 'about';
    protected string $description = 'About this bot.';

    public function getListener(): callable
    {
        return function (Interaction $interaction){
            $builder = $this->spud->getSimpleResponseBuilder();
            $builder->setTitle('About');

            $context = [
                'version' => Spud::getVersionString(),
                'applicationOwnerId' => $this->discord->application->owner->id,
            ];

            $message = $this->spud->getTwig()->render('about.twig', $context);
            $builder->setDescription($message);

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        };
    }
}