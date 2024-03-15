<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Carbon\Carbon;
use Discord\Parts\Part;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;
use Spudbot\Model\Reminder;

/**
 * @method Reminder findById(string $id)
 * @method Reminder save(Reminder $model)
 * @method bool remove(Reminder $model)
 */
class ReminderRepository extends AbstractRepository
{

    protected array $endpoints = [
        'default' => 'reminders',
        'put' => 'put|reminders/:id',
        'delete' => 'delete|members/:id',
    ];

    public function findElapsed(): Collection
    {
        return $this->find([
            'query' => [
                'has_passed' => Carbon::now()->toIso8601String(),
            ],
        ]);
    }

    public function findWithPart(Part $part): void
    {
        throw new OutOfBoundsException('Reminders cannot be located with a part.');
    }

    public function hydrate(array $fields): Reminder
    {
        return Reminder::create([
            'externalId' => $fields['external_id'],
            'description' => $fields['description'],
            'mentionRole' => $fields['mention_role'],
            'scheduledAt' => $fields['scheduled_at'],
            'repeats' => $fields['repeats'],
            'guild' => Guild::create($fields['guild']),
            'channel' => Channel::create($fields['channel']),
            'createdAt' => $fields['created_at'],
            'updatedAt' => $fields['updated_at'],
        ]);
    }
}
