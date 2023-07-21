<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Meme;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class SuperiorSteak extends IBindableEvent
{
    private string $reaction = ':bonk:1114416108385095700';

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            $keywords = [
                'pats',
                'genos',
            ];

            if (stripos('steak', $message->content) && $this->stringContains($message->content, $keywords)) {
                $message->react($this->reaction);
            }
        };
    }

    private function stringContains($string, array $array): bool
    {
        $words = explode(' ', $string);
        foreach ($words as $word) {
            foreach ($array as $matchingWord) {
                similar_text($word, $matchingWord, $percent);
                if ($percent > 65) {
                    return true;
                }
            }
        }
        return false;
    }
}