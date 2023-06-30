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

class WhyyyHelicopter extends IBindableEvent
{
    private string $whyy = ':whyy:1115394039815090196';
    private string $helicopter = '🚁';

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            $keywords = [
                'why helicopter',
                'whyy',
            ];

            if ($this->stringContains($message->content, $keywords)) {
                $message->react($this->whyy);
                $message->react($this->helicopter);
            }
        };
    }

    private function stringContains($string, array $array)
    {
        foreach ($array as $a) {
            if (stripos($string, $a) !== false) {
                return true;
            }
        }
        return false;
    }
}