<?php

declare(strict_types=1);

namespace Spudbot\Bindable\SubCommand;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\ISubCommand;
use Spudbot\Model\Member;

class UserLeaderboard extends ISubCommand
{
    protected string $subCommand = 'leaderboard';
    private int $max = 50;
    private int $default = 35;

    public function execute(?Interaction $interaction): void
    {
        $builder = $this->spud->getSimpleResponseBuilder();
        $limit = $this->options['limit']->value ?? $this->default;
        if ($limit > $this->max) {
            $limit = $this->max;
        }

        $guild = $this->spud->getGuildRepository()->findByPart($interaction->guild);
        $members = $this->spud->getMemberRepository()
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
}