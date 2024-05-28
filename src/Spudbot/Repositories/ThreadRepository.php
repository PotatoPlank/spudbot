<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Discord\Parts\Part;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Model\Thread;

/**
 * @method Thread findById(string $id)
 * @method Thread save(Thread $model)
 * @method bool remove(Thread $model)
 */
class ThreadRepository extends AbstractRepository
{
    protected string $model = Thread::class;
    protected array $updateFilter = [
        'discord_id',
        'guild',
        'channel',
    ];

    protected array $endpoints = [
        'default' => 'threads',
        'put' => 'put|threads/:id',
        'delete' => 'delete|threads/:id',
    ];

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function findWithPart(Part $part): ?Thread
    {
        return $this->findByDiscordId($part->id)->first();
    }
}
