<?php

namespace Spudbot\Bindable\Command;

use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;

class Version extends IBindableCommand
{
    protected string $name = 'version';
    protected string $description = 'Returns the latest bot version information.';
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
}