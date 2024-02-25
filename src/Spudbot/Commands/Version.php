<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;

class Version extends AbstractCommandSubscriber
{
    public function getCommandName(): string
    {
        return 'version';
    }

    public function getCommandDescription(): string
    {
        return 'Returns the latest bot version information.';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }

        $simpleResponse = $this->spud->getSimpleResponseBuilder();
        $date = Carbon::parse(trim(exec('git log -n1 --pretty=%ci HEAD')));

        $simpleResponse->setTitle('Version');
        $simpleResponse->setDescription("Latest code is from: " . $date->toIso8601String());

        $interaction->respondWithMessage($simpleResponse->getEmbeddedMessage());
    }
}