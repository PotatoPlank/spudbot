<?php

namespace Spudbot\Bindable\Command;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;

class Restart extends IBindableCommand
{
    protected string $name = 'restart';
    protected string $description = 'Restart Spud to fix issues or load new functionality.';
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
}