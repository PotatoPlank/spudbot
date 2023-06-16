<?php

namespace Spudbot\Bindable\Command;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Command as CommandPart;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class PurgeCommands extends BindableCommand
{
    public function getListener(): callable
    {
        return function (Interaction $interaction){
            $builder = $this->spud->getSimpleResponseBuilder();

            if($this->discord->application->owner->id === $interaction->user->id){
                $builder->setTitle('Purge Commands');
                $builder->setDescription('Commands are now being purged. The bot will restart when it\'s complete.');
                $interaction->respondWithMessage($builder->getEmbeddedMessage(), true)->done(function(){
                    $this->discord->application->commands->freshen()->then(function ($commands) {
                        foreach ($commands as $command) {
                            $this->discord->getLogger()->alert("Purging the command: {$command->name}");
                            $this->discord->application->commands->delete($command);
                        }
                        exit;
                    });
                });
            }else{
                $builder->setTitle('Invalid Permissions for Purge Commands');
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
        return 'purge_commands';
    }

    public function getDescription(): string
    {
        return 'Purges bound commands within the bot.';
    }
}