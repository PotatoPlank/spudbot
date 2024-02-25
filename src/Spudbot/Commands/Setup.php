<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\Model\Guild;

class Setup extends AbstractCommandSubscriber
{

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
        $builder = $this->spud->getSimpleResponseBuilder();
        if (!$interaction->member->permissions->manage_guild) {
            $builder->setTitle('Invalid Permissions for Setup');
            $builder->setDescription('You don\'t have the necessary permissions to run this command.');

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
            return;
        }

        $interaction->guild->channels->fetch($interaction->channel_id)->done(
            function (Channel $channel) use ($interaction, $builder) {
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

                try {
                    $guild = $this->spud->guildRepository->findByPart($interaction->guild);
                } catch (\OutOfBoundsException $exception) {
                    $guild = new Guild();
                    $guild->setDiscordId($interaction->guild_id);
                }

                $guild->setOutputChannelId($channelId);
                if ($isThread) {
                    $guild->setOutputThreadId($threadId);
                }
                $this->spud->guildRepository->save($guild);

                $builder->setTitle('Setup complete');
                $builder->setDescription(
                    "Set the guild output location to <#{$guild->getOutputLocationId()}>."
                );

                $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
            }
        );
    }
}
