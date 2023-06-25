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

class AutomaticIntroThreads extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            if ($message->channel_id === '1114365925366440038') {
                $memberName = $message->member->nick ?? $message->member->displayname;
                $context = [
                    'memberName' => $memberName,
                ];
                $message->react(':dogevibe:1115010156728680478');
                $message->react(':mokka:1115005842102681770');
                $message->startThread($this->spud->twig->render('user/intro_title.twig', $context));
            }
        };
    }
}