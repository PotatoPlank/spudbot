<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Ready;

use BadMethodCallException;
use Exception;
use Spudbot\Bot\ApplicationVersion;
use Spudbot\Bot\Events;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Guild;

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
                throw new BadMethodCallException("Invalid guild {$this->spud->logGuild->getDiscordId()}");
            }
            $output = $this->spud->logGuild->getChannelThreadPart(Guild::BOT_LOG_CHANNEL, $part);
        } catch (Exception $exception) {
            $this->spud->logger
                ->error($exception->getMessage());
            $this->spud->logger->error($exception->getMessage(), ['exception' => $exception,]);
            return;
        }

        $this->spud->interact()
            ->setTitle('Bot started')
            ->setDescription("Spudbot started. " . ApplicationVersion::get())
            ->sendTo($output);
    }

    public function canRun(): bool
    {
        return !empty($this->spud->logGuild) && $_ENV['SENTRY_ENV'] !== 'dev';
    }
}
