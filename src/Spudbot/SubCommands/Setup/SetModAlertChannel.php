<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\SubCommands\Setup;


use BadMethodCallException;
use DI\Attribute\Inject;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\Guild;
use Spudbot\Services\ChannelService;
use Spudbot\Services\GuildService;
use Spudbot\Services\ThreadService;
use Spudbot\SubCommands\AbstractSubCommandSubscriber;
use Spudbot\Util\ChannelTypes;

class SetModAlertChannel extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected ThreadService $threadService;

    public function getCommandName(): string
    {
        return 'mod_alert_channel';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            throw new BadMethodCallException('Setting a Mod Alert Channel requires an interaction.');
        }
        $channelId = $this->options['channel_id']->value;
        $interaction->guild->channels->fetch($channelId)
            ->then(function (Channel $channelPart) use ($interaction) {
                $isThread = ChannelTypes::isThread($channelPart->type);

                $guild = $this->guildService->findOrCreateWithPart($interaction->guild);

                if ($isThread) {
                    $interaction->guild->channels->fetch($channelPart->parent_id)
                        ->then(function (Channel $parentPart) use ($channelPart, $guild) {
                            $channel = $this->channelService->findOrCreateWithPart($parentPart);
                            $thread = $this->threadService->findWithDiscordId(
                                $channelPart->id,
                                $channelPart->guild->id
                            );
                            if (!$thread) {
                                $thread = $this->threadService->makeWithChannel($channelPart->id, $channel);
                            }
                            $this->save($guild, $channel->getDiscordId(), $thread->getDiscordId());
                        });
                } else {
                    $channel = $this->channelService->findOrCreateWithPart($channelPart);
                    $this->save($guild, $channel->getDiscordId());
                }

                $this->spud->interact()
                    ->setTitle('Set Mod Alert Channel')
                    ->setDescription(
                        "Set the mod alert channel."
                    )->respondTo($interaction, true);
            });
    }

    protected function save(Guild $guild, ?string $channelId, ?string $threadId = null): void
    {
        $guild->channelModAlertId = $channelId;
        $guild->channelThreadModAlertId = $threadId;
        $this->guildService->save($guild);
    }
}
