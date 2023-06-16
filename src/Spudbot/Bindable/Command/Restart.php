<?php

namespace Spudbot\Bindable\Command;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Command as CommandPart;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class Restart extends BindableCommand
{
    public function getListener(): callable
    {
        return function (Interaction $interaction){
            $builder = $this->spud->getSimpleResponseBuilder();

            if($this->discord->application->owner->id === $interaction->user->id){
                $builder->setTitle('Restart');
                $builder->setDescription('The bot will now restart.');

                $interaction->respondWithMessage($builder->getEmbeddedMessage(), true)->done(function(){
                    exit;
                });
            }else{
                $builder->setTitle('Invalid Permissions for Restart');
                $builder->setDescription('You don\'t have the necessary permissions to run this command.');

                $interaction->respondWithMessage($builder->getEmbeddedMessage());
            }
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
        return 'restart';
    }

    public function getDescription(): string
    {
        return 'Restart Spud to fix issues or load new functionality.';
    }
}