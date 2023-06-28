<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Spudbot\Helpers\Collection;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;

abstract class IMemberRepository
{
    abstract public function findById(string|int $id): Member;

    abstract public function findByDiscordId(string $discordId, string $discordGuildId): Member;

    abstract public function findByPart(\Discord\Parts\User\Member $member): Member;

    abstract public function findByGuild(Guild $guild): Collection;

    abstract public function getAll(): Collection;

    abstract public function getEventAttendance(Member $member): Collection;

    abstract public function save(Member $member): bool;

    abstract public function saveMemberEventAttendance(EventAttendance $eventAttendance): bool;

    abstract public function remove(Member $member): bool;
}