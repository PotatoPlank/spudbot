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
use Discord\Parts\Interactions\Interaction;
use Spudbot\Services\ChannelService;
use Spudbot\Services\GuildService;
use Spudbot\Services\ThreadService;
use Spudbot\SubCommands\AbstractSubCommandSubscriber;

class SetVerifiedRole extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected ThreadService $threadService;

    public function getCommandName(): string
    {
        return 'verified_role';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            throw new BadMethodCallException('Setting a Verified Role requires an interaction.');
        }
        $roleId = $this->options['role_id']->value;
        $guild = $this->guildService->findOrCreateWithPart($interaction->guild);

        $guild->setVerifiedMembersRoleId($roleId);
        $this->guildService->save($guild);

        $this->spud->interact()
            ->setTitle('Set Verified Role')
            ->setDescription(
                "Set the verified role."
            )->respondTo($interaction, true);
    }
}
