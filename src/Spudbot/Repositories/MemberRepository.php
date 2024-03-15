<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Discord\Parts\Part;
use Spudbot\Helpers\Collection;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;

/**
 * @method Member findById(string $id)
 * @method Member save(Member $model)
 * @method bool remove(Member $model)
 */
class MemberRepository extends AbstractRepository
{

    protected array $endpoints = [
        'default' => 'members',
        'put' => 'put|members/:id',
        'delete' => 'delete|members/:id',
    ];

    public function findByGuild(Guild $guild): Collection
    {
        $response = $this->find([
            'query' => [
                'guild' => $guild->getId(),
            ],
        ]);

        return $response->first();
    }

    public function getTopCommentersByGuild(Guild $guild, $limit = 10): Collection
    {
        return $this->find([
            'query' => [
                'sort' => 'total_comments',
                'direction' => 'desc',
                'guild_discord_id' => $guild->getDiscordId(),
                'limit' => $limit,
            ],
        ]);
    }

    public function findWithPart(Part|\Discord\Parts\User\Member $part): Member
    {
        return $this->findByDiscordId($part->id, $part->guild->id)->first();
    }

    public function hydrate(array $fields): Member
    {
        return Member::create([
            'id' => $fields['external_id'],
            'discordId' => $fields['discord_id'],
            'totalComments' => $fields['total_comments'],
            'username' => $fields['username'],
            'guild' => Guild::create($fields['guild']),
            'verifiedBy' => !empty($fields['verified_by']) ? Member::create($fields['verified_by']) : null,
            'createdAt' => $fields['created_at'],
            'modifiedAt' => $fields['updated_at'],
        ]);
    }
}
