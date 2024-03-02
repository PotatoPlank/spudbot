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
        try {
            $part = $this->spud->discord->guilds->get('id', $this->spud->logGuild->getDiscordId());
            if (!$part) {
                throw new \BadMethodCallException("Invalid guild {$this->spud->logGuild->getDiscordId()}");
            }
            $output = $this->spud->logGuild->getOutputPart($part);
        } catch (\Exception $exception) {
            $this->spud->discord->getLogger()
                ->error($exception->getMessage());
            return;
        }

        $builder = $this->spud->interact()
            ->setTitle('Bot started')
            ->setDescription("Spudbot started. " . ApplicationVersion::get());
        $output->sendMessage($builder->build());
    }

    public function canRun(): bool
    {
        return !empty($this->spud->logGuild) && $_ENV['SENTRY_ENV'] !== 'dev';
    }
}
