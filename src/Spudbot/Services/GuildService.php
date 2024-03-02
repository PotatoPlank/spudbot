<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Model\Guild;
use Spudbot\Repository\Api\GuildRepository;

class GuildService
{
    public function __construct(public GuildRepository $guildRepository)
    {
    }

    public function findWithPart(\Discord\Parts\Guild\Guild $guild): Guild
    {
        try {
            return $this->guildRepository->findByPart($guild);
        } catch (OutOfBoundsException $exception) {
            return $this->guildRepository->save(Guild::create([
                'discordId' => $guild->id,
                'outputChannelId' => null,
                'outputThreadId' => null,
            ]));
        }
    }
}
