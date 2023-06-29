<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Message;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class SprayUser extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            $meows = [
                'meow',
                'miao',
                'cat',
                'woof',
                'owner',
                'm___',
            ];
            $wordCount = count(explode(' ', $message->content));
            $hasAnimal = $this->stringContains($message->content, $meows);
            $spray = ['992794792419401728', '1064128213518921758', '82628865792409600', '147494168128651264',];
            $isSprayable = in_array($message->member->id, $spray);
            $isSprayable = true;

            if ($isSprayable && ($hasAnimal || $wordCount < 2)) {
                $message->react(':nospray:1115701447569461349');
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