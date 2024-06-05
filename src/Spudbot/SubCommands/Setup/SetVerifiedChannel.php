<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\SubCommands\Setup;


use BadMethodCallException;
use DI\Attribute\Inject;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Services\ChannelService;
use Spudbot\Services\GuildService;
use Spudbot\Services\ThreadService;
use Spudbot\SubCommands\AbstractSubCommandSubscriber;
use Spudbot\Util\ChannelTypes;

class SetVerifiedChannel extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected ThreadService $threadService;

    public function getCommandName(): string
    {
        return 'verified_channel';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            throw new BadMethodCallException('Setting a Verified Channel Channel requires an interaction.');
        }
        $channelId = $this->options['channel_id']->value;
        $interaction->guild->channels->fetch($channelId)
            ->done(function (Channel $channelPart) use ($interaction) {
                $isThread = ChannelTypes::isThread($channelPart->type);
                if ($isThread) {
                    $this->spud->interact()
                        ->setTitle('Set Verified Channel')
                        ->setDescription(
                            "Unable to specify a thread."
                        )->respondTo($interaction, true);
                    return;
                }

                $guild = $this->guildService->findOrCreateWithPart($interaction->guild);

                $channel = $this->channelService->findOrCreateWithPart($channelPart);
                $guild->verifiedMembersChannelId = $channel->getDiscordId();
                $this->guildService->save($guild);
            });

        $this->spud->interact()
            ->setTitle('Set Verified Channel')
            ->setDescription(
                "Set the verified channel."
            )->respondTo($interaction, true);
    }
}
