<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Bindable\SubCommand\Coven;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\ISubCommand;

class Give extends ISubCommand
{
    protected string $subCommand = 'give';

    public function execute(?Interaction $interaction): void
    {
        if (!$interaction) {
            throw new \RuntimeException('Give Coven requires an interaction.');
        }
        $builder = $this->spud->getSimpleResponseBuilder();
        $builder->setTitle('Give Coven');

        $memberId = $this->options['user']->value;
        $sourceMemberId = $interaction->member->id;
        $channelId = '1114365925366440043';
        $channel = $interaction->guild->channels->get('id', $channelId);
        $member = $interaction->guild->members->get('id', $memberId);
        if ($channel && $member) {
            $channel->setPermissions($member, [
                'view_channel',
            ], [], 'Moderator gave access.');

            $context = [
                'memberId' => $memberId,
                'sourceMemberId' => $sourceMemberId,
            ];
            $message = $this->spud->twig->render('user/give_coven.twig', $context);
            $builder->setDescription($message);
        } else {
            $builder->setDescription('Discord member and/or channel is not yet cached, please try again later.');
        }

        $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
    }
}