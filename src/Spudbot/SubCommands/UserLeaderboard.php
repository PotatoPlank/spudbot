<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\SubCommands;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractSubCommandSubscriber;
use Spudbot\Model\Member;

class UserLeaderboard extends AbstractSubCommandSubscriber
{
    private int $max = 50;
    private int $default = 35;

    public function update(?Interaction $interaction = null): void
    {
        $builder = $this->spud->getSimpleResponseBuilder();
        $limit = $this->options['limit']->value ?? $this->default;
        if ($limit > $this->max) {
            $limit = $this->max;
        }

        $guild = $this->spud->guildRepository->findByPart($interaction->guild);
        $members = $this->spud->memberRepository
            ->getTopCommentersByGuild($guild, $limit);

        $title = 'User Leaderboard';
        $message = '';
        if (!$members->empty()) {
            $position = 1;
            /**
             * @var $member Member
             */
            foreach ($members as $member) {
                $message .= "{$position}) <@{$member->getDiscordId()}> ({$member->getUsername()}) has made {$member->getTotalComments()} comments." . PHP_EOL;

                $position++;
            }
        } else {
            $message = 'Unable to retrieve leaderboard.';
        }


        $builder->setTitle($title);
        $builder->setDescription($message);

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }

    public function getCommandName(): string
    {
        return 'leaderboard';
    }
}
