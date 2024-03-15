<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Discord\Parts\Part;
use Spudbot\Model\Guild;

/**
 * @method Guild findById(string $id)
 * @method Guild save(Guild $model)
 * @method bool remove(Guild $model)
 */
class GuildRepository extends AbstractRepository
{
    protected array $endpoints = [
        'default' => 'guilds',
        'put' => 'put|guilds/:id',
        'delete' => 'delete|guilds/:id',
    ];

    public function findWithPart(Part $part): Guild
    {
        return $this->findByDiscordId($part->id)->first();
    }

    public function hydrate(array $fields): Guild
    {
        return Guild::create([
            'external_id' => $fields['external_id'],
            'discordId' => $fields['discord_id'],
            'outputChannelId' => $fields['channel_announce_id'],
            'outputThreadId' => $fields['channel_thread_announce_id'],
            'createdAt' => $fields['created_at'],
            'updatedAt' => $fields['updated_at'],
        ]);
    }
}
