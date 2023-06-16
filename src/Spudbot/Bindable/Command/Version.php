<?php

namespace Spudbot\Bindable\Command;

use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Command as CommandPart;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Bot\Spud;

class Version extends BindableCommand
{
    public function getListener(): callable
    {
        return function (Interaction $interaction){
            $simpleResponse = $this->spud->getSimpleResponseBuilder();
            $date = Carbon::parse(trim(exec('git log -n1 --pretty=%ci HEAD')));

            $simpleResponse->setTitle('Version');
            $simpleResponse->setDescription("Latest code is from: " . $date->toIso8601String());

            $interaction->respondWithMessage($simpleResponse->getEmbeddedMessage());
        };
    }

    public function getCommand(): CommandPart
    {
        $attributes = [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];

        return new Command($this->discord, $attributes);
    }

    public function getName(): string
    {
        return 'version';
    }

    public function getDescription(): string
    {
        return 'Returns the latest bot version information';
    }
}