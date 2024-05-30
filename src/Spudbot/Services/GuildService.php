<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Helpers\Collection;
use Spudbot\Model\Guild;
use Spudbot\Repositories\GuildRepository;
use Spudbot\Repositories\MemberRepository;

class GuildService
{
    public function __construct(
        protected GuildRepository $guildRepository,
        protected MemberRepository $memberRepository
    ) {
    }

    public function findOrCreateWithPart(\Discord\Parts\Guild\Guild $guild): Guild
    {
        try {
            $model = $this->guildRepository->findWithPart($guild);
            if ($model) {
                return $model;
            }
            throw new OutOfBoundsException('Does not exist.');
        } catch (OutOfBoundsException $exception) {
            return $this->save($this->guildRepository->new([
                'discord_id' => $guild->id,
                'channel_announce_id' => null,
                'channel_thread_announce_id' => null,
            ]));
        }
    }

    public function save(Guild $guild): Guild
    {
        return $this->guildRepository->save($guild);
    }

    public function getTopPosters(Guild $guild, $limit = 10): Collection
    {
        return $this->memberRepository->getTopCommentersByGuild($guild, $limit);
    }

    /**
     * @param callable|null $filter
     * @return Collection|Guild[]
     * @throws ApiException
     * @throws ApiRequestFailure
     */
    public function all(?callable $filter = null): Collection
    {
        $guilds = $this->guildRepository->all();
        if ($filter !== null) {
            $guilds->filter($filter);
        }
        return $guilds;
    }
}
