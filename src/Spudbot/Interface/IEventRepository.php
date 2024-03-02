<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Discord\Parts\Guild\ScheduledEvent;
use Spudbot\Helpers\Collection;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use stdClass;

abstract class IEventRepository
{
    abstract public function findById(string|int $id): Event;

    abstract public function findByDiscordId(string $discordId, string $discordGuildId): Event;

    abstract public function findBySeshId(string $seshId): Event;

    abstract public function findByPart(stdClass|ScheduledEvent $event): Event;

    abstract public function findByGuild(Guild $guild): Collection;

    abstract public function getAll(): Collection;

    abstract public function getAttendanceByEvent(Event $event): Collection;

    abstract public function getAttendanceByMemberAndEvent(Member $member, Event $event): EventAttendance;

    abstract public function save(Event $event): Event;

    abstract public function remove(Event $event): bool;
}
