<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use Spudbot\Helpers\Collection;
use Spudbot\Model\Reminder;
use Spudbot\Repositories\ReminderRepository;

class ReminderService
{
    public function __construct(protected ReminderRepository $reminderRepository)
    {
    }


    public function save(Reminder $reminder): Reminder
    {
        return $this->reminderRepository->save($reminder);
    }

    public function remove(Reminder $reminder): bool
    {
        return $this->reminderRepository->remove($reminder);
    }

    public function findElapsed(): Collection
    {
        return $this->reminderRepository->findElapsed();
    }
}
