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

class GoodMorning extends IBindableEvent
{
    private string $reaction = ':sun_with_face:';

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            $keywords = [
                'good morning',
            ];

            if ($this->stringContains(strtolower($message->content), $keywords)) {
                $message->react($this->reaction);
            }
        };
    }

    private function stringContains($string, array $array): bool
    {
        foreach ($array as $a) {
            if (stripos($string, $a) !== false) {
                return true;
            }
        }
        return false;
    }
}