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
    private array $sprays = [];

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            $keywords = [
                'meow',
                'woof',
                'my owner',
            ];

            if ($this->stringContains($message->content, $keywords)) {
                if (!isset($this->sprays[$message->guild->id])) {
                    $this->sprays[$message->guild->id] = 0;
                }
                $this->sprays[$message->guild->id]++;
                $sprayCount = $this->sprays[$message->guild->id];
                $reaction = ':nospray:1115701447569461349';

                if ($sprayCount % 10 !== 0) {
                    $message->react($reaction);
                } else {
                    $refill = 'https://www.contemporist.com/wp-content/uploads/2015/11/bu-water_071115_04.gif';
                    $response = $this->spud->getSimpleResponseBuilder();
                    $response->setTitle('We\'ll Be Right Back');
                    $response->setOptions(['image' => ['url' => $refill]]);
                    $message->reply($response->getEmbeddedMessage());


                    $this->discord->getLoop()->addTimer(random_int(2, 15), function () use ($message, $reaction) {
                        $message->react($reaction);
                    });
                }
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