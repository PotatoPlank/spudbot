<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\SubCommands;


use DI\Attribute\Inject;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractSubCommandSubscriber;
use Spudbot\Model\Member;
use Spudbot\Services\GuildService;

class UserLeaderboard extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected GuildService $guildService;
    private int $max = 50;
    private int $default = 35;

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $limit = $this->options['limit']->value ?? $this->default;
        if ($limit > $this->max) {
            $limit = $this->max;
        }

        $guild = $this->guildService->findWithPart($interaction->guild);
        $members = $this->spud->memberRepository
            ->getTopCommentersByGuild($guild, $limit);

        $message = 'Unable to retrieve leaderboard.';
        if (!$members->empty()) {
            $position = 1;
            $message = '';
            /**
             * @var $member Member
             */
            foreach ($members as $member) {
                $message .= "$position) <@{$member->getDiscordId()}> ({$member->getUsername()})";
                $message .= " has made {$member->getTotalComments()} comments." . PHP_EOL;

                $position++;
            }
        }


        $this->spud->interact()
            ->setTitle('User Leaderboard')
            ->setDescription($message)
            ->respondTo($interaction);
    }

    public function getCommandName(): string
    {
        return 'leaderboard';
    }
}
