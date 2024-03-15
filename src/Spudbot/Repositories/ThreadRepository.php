<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Discord\Parts\Part;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;

/**
 * @method Thread findById(string $id)
 * @method Thread save(Thread $model)
 * @method bool remove(Thread $model)
 */
class ThreadRepository extends AbstractRepository
{

    protected array $endpoints = [
        'default' => 'threads',
        'put' => 'put|threads/:id',
        'delete' => 'delete|threads/:id',
    ];

    public function findWithPart(Part $part): Thread
    {
        return $this->findByDiscordId($part->id)->first();
    }

    public function hydrate(array $fields): Thread
    {
        return Thread::create([
            'externalId' => $fields['external_id'],
            'discordId' => $fields['discord_id'],
            'guild' => Guild::create($fields['guild']),
            'channel' => Channel::create($fields['channel']),
            'tag' => $fields['tag'],
            'createdAt' => $fields['created_at'],
            'updatedAt' => $fields['updated_at'],
        ]);
    }
}
