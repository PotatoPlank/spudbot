<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use DI\Attribute\Inject;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\Services\GuildService;

class Setup extends AbstractCommandSubscriber
{
    #[Inject]
    protected GuildService $guildService;

    public function getCommandName(): string
    {
        return 'setup';
    }

    public function getCommandDescription(): string
    {
        return 'Setup the guild and the selected channel as the log output location.';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        if (!$interaction->member->permissions->manage_guild) {
            $this->spud->interact()
                ->error('You don\'t have the necessary permissions to run this command.')
                ->respondTo($interaction);
            return;
        }

        $interaction->guild->channels->fetch($interaction->channel_id)
            ->done(function (Channel $channel) use ($interaction) {
                $channelId = $channel->id;
                $threadTypes = [
                    Channel::TYPE_ANNOUNCEMENT_THREAD,
                    Channel::TYPE_PUBLIC_THREAD,
                    Channel::TYPE_PRIVATE_THREAD
                ];
                $isThread = in_array($channel->type, $threadTypes, true);

                if ($isThread) {
                    $channelId = $channel->parent_id;
                    $threadId = $channel->id;
                }

                $guild = $this->guildService->findWithPart($interaction->guild);

                $guild->setOutputChannelId($channelId);
                if ($isThread) {
                    $guild->setOutputThreadId($threadId);
                }
                $this->spud->guildRepository->save($guild);

                $this->spud->interact()
                    ->setTitle('Setup complete')
                    ->setDescription(
                        "Set the guild output location to <#{$guild->getOutputLocationId()}>."
                    )->respondTo($interaction, true);
            });
    }
}
