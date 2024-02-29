<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Ready;

use Spudbot\Bot\ApplicationVersion;
use Spudbot\Bot\Events;
use Spudbot\Interface\AbstractEventSubscriber;

class LogGuildChannel extends AbstractEventSubscriber
{

    public function getEventName(): string
    {
        return Events::READY->value;
    }

    public function update(): void
    {
        if (!empty($this->spud->logGuild) && $_ENV['SENTRY_ENV'] !== 'dev') {
            $output = $this->spud->discord->guilds->get('id', $this->spud->logGuild->getDiscordId())->channels->get(
                'id',
                $this->spud->logGuild->getOutputChannelId()
            );
            if (!empty($this->spud->logGuild->getOutputThreadId())) {
                $output = $output->threads->get('id', $this->spud->logGuild->getOutputThreadId());
            }
            if (!$output) {
                return;
            }
            $builder = $this->spud->getSimpleResponseBuilder();
            $builder->setTitle('Bot started')
                ->setDescription("Spudbot started. " . ApplicationVersion::get());
            $output->sendMessage($builder->getEmbeddedMessage());
        }
    }
}
