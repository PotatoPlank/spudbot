<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Model\Channel;
use Spudbot\Repositories\ChannelRepository;

class ChannelService
{
    public function __construct(public ChannelRepository $channelRepository, public GuildService $guildService)
    {
    }

    public function findOrCreateWithPart(\Discord\Parts\Channel\Channel $channel): Channel
    {
        try {
            $model = $this->channelRepository->findWithPart($channel);
            if ($model) {
                return $model;
            }
            throw new OutOfBoundsException('Does not exist.');
        } catch (OutOfBoundsException $exception) {
            return $this->save(Channel::create([
                'discordId' => $channel->id,
                'guild' => $this->guildService->findOrCreateWithPart($channel->guild),
            ]));
        }
    }

    public function save(Channel $channel): Channel
    {
        return $this->channelRepository->save($channel);
    }
}
