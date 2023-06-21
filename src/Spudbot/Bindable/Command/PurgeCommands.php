<?php

namespace Spudbot\Bindable\Command;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;

class PurgeCommands extends IBindableCommand
{
    protected string $name = 'purge_commands';
    protected string $description = 'Purges bound commands within the bot.';
    public function getListener(): callable
    {
        return function (Interaction $interaction){
            $builder = $this->spud->getSimpleResponseBuilder();

            if($this->discord->application->owner->id === $interaction->user->id){
                $builder->setTitle('Purge Commands');
                $builder->setDescription('Commands are now being purged. The bot will restart when it\'s complete.');
                $interaction->respondWithMessage($builder->getEmbeddedMessage())->done(function(){
                    $this->discord->application->commands->freshen()->then(function ($commands) {
                        foreach ($commands as $i => $command) {
                            $this->discord->getLogger()->alert("Purging the command: {$command->name}");
                            $exit = $i === (count($commands) - 1) ? function(){exit;} : null;
                            $this->discord->application->commands->delete($command)->done($exit);
                        }
                    });
                });
            }else{
                $builder->setTitle('Invalid Permissions for Purge Commands');
                $builder->setDescription('You don\'t have the necessary permissions to run this command.');

                $interaction->respondWithMessage($builder->getEmbeddedMessage());
            }
        };
    }
}