<?php

namespace Spudbot\Bindable\Command;

use Carbon\Carbon;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Command as CommandPart;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class About extends BindableCommand
{
    protected string $name = 'about';
    protected string $description = 'About this bot.';

    public function getListener(): callable
    {
        return function (Interaction $interaction){
            $builder = $this->spud->getSimpleResponseBuilder();
            $lastCommit = Carbon::parse(trim(exec('git log -n1 --pretty=%ci HEAD')));

            $message = "SpudBot ({$lastCommit->toIso8601String()})" . PHP_EOL;
            $message .= "Author: <@171444377279922176>" . PHP_EOL;
            $message .= "This instance is owned by <@{$this->discord->application->owner->id}>" . PHP_EOL;
            $message .= "License: GNU GPLv3" . PHP_EOL;
            $message .= "Github: https://github.com/PotatoPlank/spudbot" . PHP_EOL;

            $builder->setTitle('About');
            $builder->setDescription($message);

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        };
    }

    public function getCommand(): Command
    {
        $attributes = [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];
        $command = CommandBuilder::new();
        $command->setName($this->getName());
        $command->setDescription($this->getDescription());

        return new Command($this->discord, $attributes);
    }
}