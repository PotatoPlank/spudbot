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
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;

/**
 * @method Channel save(Channel $model)
 * @method bool remove(Channel $model)
 */
class ChannelRepository extends AbstractRepository
{

    protected array $endpoints = [
        'default' => 'channels',
        'put' => 'put|channels/:id',
        'delete' => 'delete|channels/:id',
    ];

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function findWithPart(Part $part): ?Channel
    {
        return $this->findByDiscordId($part->id)->first();
    }

    public function hydrate(array $fields): Channel
    {
        return Channel::create([
            'external_id' => $fields['external_id'],
            'discord_id' => $fields['discord_id'],
            'guild' => Guild::create($fields['guild']),
            'created_at' => $fields['created_at'],
            'updated_at' => $fields['updated_at'],
        ]);
    }
}
