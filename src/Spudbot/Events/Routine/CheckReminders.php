<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Routine;


use Spudbot\Bot\Events;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Model\Reminder;
use Spudbot\Util\Recurrence;

class CheckReminders extends AbstractEventSubscriber
{
    public function getEventName(): string
    {
        return Events::EVERY_MINUTE->value;
    }

    public function update(): void
    {
        $reminders = $this->spud->reminderRepository->findElapsed();
        if ($reminders->empty()) {
            return;
        }
        $builder = $this->spud->interact()
            ->setTitle('Reminder');
        /**
         * @var Reminder $reminder
         */
        foreach ($reminders as $reminder) {
            $guild = $this->spud->discord->guilds->get('id', $reminder->getGuild()->getDiscordId());
            if (!$guild) {
                $this->spud->discord->getLogger()
                    ->error("Unable to access the guild {$reminder->getGuild()->getDiscordId()}.");
                continue;
            }
            $channel = $guild->channels->get('id', $reminder->getChannel()->getDiscordId());
            if (!$channel) {
                $this->spud->discord->getLogger()
                    ->error("Unable to access the channel {$reminder->getChannel()->getDiscordId()}.");
                continue;
            }

            $builder->setDescription($reminder->getDescription())
                ->sendTo($channel)
                ->done(function () use ($reminder) {
                    if (empty($reminder->getRepeats())) {
                        $this->spud->reminderRepository->remove($reminder);
                        return;
                    }
                    $scheduled = $reminder->getScheduledAt();
                    $interval = $reminder->getRepeats();
                    $nextOccurrence = Recurrence::getNextDateTimeFromInterval(
                        $scheduled,
                        $interval
                    );
                    $reminder->setScheduledAt($nextOccurrence);
                    $this->spud->reminderRepository->save($reminder);
                });
        }
    }
}
