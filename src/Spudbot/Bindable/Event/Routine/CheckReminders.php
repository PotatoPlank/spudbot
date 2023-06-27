<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Routine;


use Spudbot\Bot\Events;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\Reminder;

class CheckReminders extends IBindableEvent
{

    public function getBoundEvent(): string|Events
    {
        return Events::EVERY_MINUTE->value;
    }

    public function getListener(): callable
    {
        return function () {
            $reminders = $this->spud->getReminderRepository()->findElapsed();
            if (!$reminders->empty()) {
                $builder = $this->spud->getSimpleResponseBuilder();
                $builder->setTitle('Reminder');
                /**
                 * @var Reminder $reminder
                 */
                foreach ($reminders as $reminder) {
                    $guild = $this->discord->guilds->get('id', $reminder->getGuild()->getDiscordId());
                    if ($guild) {
                        $channel = $guild->channels->get('id', $reminder->getChannel()->getDiscordId());
                        if ($channel) {
                            $builder->setDescription($reminder->getDescription());

                            $channel->sendMessage($builder->getEmbeddedMessage())->done(
                                function () use ($reminder) {
                                    $this->spud->getReminderRepository()->remove($reminder);
                                }
                            );
                        }
                    }
                }
            }
        };
    }
}