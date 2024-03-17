<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Meme;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;

class SprayUser extends AbstractEventSubscriber
{
    private array $sprays = [];
    private string $reaction = ':nospray:1115701447569461349';
    private string $refill = 'https://www.contemporist.com/wp-content/uploads/2015/11/bu-water_071115_04.gif';
    private array $triggerKeywords = [
        'meow',
        'woof',
    ];

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message) {
            return;
        }

        $wordCount = str_word_count($message->content);
        $normalizedMessage = strtolower($message->content);
        if ($wordCount !== 1 || !in_array($normalizedMessage, $this->triggerKeywords, true)) {
            return;
        }

        $this->incrementSprays($message->guild->id);
        $sprayCount = $this->getSprayCount($message->guild->id);

        if ($sprayCount % 10 !== 0) {
            $message->react($this->reaction);
            return;
        }

        $this->spud->interact()
            ->setTitle('We\'ll Be Right Back')
            ->setOptions(['image' => ['url' => $this->refill]])
            ->replyTo($message)
            ->done(function (Message $responseMessage) use ($message) {
                $this->spud->discord->getLoop()
                    ->addTimer($this->getDelay(), function () use ($message, $responseMessage) {
                        $message->react($this->reaction);
                        $responseMessage->delete();
                    });
            });
    }

    public function incrementSprays(mixed $guildId): void
    {
        if (!isset($this->sprays[$guildId])) {
            $this->sprays[$guildId] = 0;
        }
        $this->sprays[$guildId]++;
    }

    public function getSprayCount(mixed $guildId): int
    {
        return $this->sprays[$guildId] ?? 0;
    }

    public function getDelay(int $min = 6, int $max = 15): int
    {
        return random_int($min, $max);
    }
}
