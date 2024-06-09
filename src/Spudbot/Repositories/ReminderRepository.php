<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Parts\Part;
use OutOfBoundsException;
use Psr\Log\LoggerInterface;
use Spudbot\Helpers\Collection;
use Spudbot\Model\Reminder;

/**
 * @method Reminder findById(string $id)
 * @method Reminder save(Reminder $model)
 * @method bool remove(Reminder $model)
 */
class ReminderRepository extends AbstractRepository
{
    #[Inject]
    protected LoggerInterface $logger;
    protected string $model = Reminder::class;
    protected array $endpoints = [
        'default' => 'reminders',
        'put' => 'put|reminders/:id',
        'delete' => 'delete|reminders/:id',
    ];
    protected array $updateFilter = [
        'guild',
        'channel',
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
}
