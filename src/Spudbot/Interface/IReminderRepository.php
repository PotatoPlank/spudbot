<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Carbon\Carbon;
use Spudbot\Helpers\Collection;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;
use Spudbot\Model\Reminder;

abstract class IReminderRepository
{
    abstract public function findById(string|int $id): Reminder;

    abstract public function findByDate(Carbon $scheduledAt): Collection;

    abstract public function findByDateTime(Carbon $scheduledAt): Reminder;

    abstract public function findByGuild(Guild $guild): Collection;

    abstract public function findByChannel(Channel $channel): Collection;

    abstract public function getAll(): Collection;


    abstract public function save(Reminder $reminder): bool;

    abstract public function remove(Reminder $reminder): bool;
}