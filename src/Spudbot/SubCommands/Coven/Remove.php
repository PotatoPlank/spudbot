<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\SubCommands\Coven;


use BadMethodCallException;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractSubCommandSubscriber;

class Remove extends AbstractSubCommandSubscriber
{
    private const COVEN_CHANNEL_ID = '1114365925366440043';

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            throw new BadMethodCallException('Remove Coven requires an interaction.');
        }
        $builder = $this->spud->interact()
            ->setTitle('Remove Coven');

        $memberId = $this->options['user']->value;
        $sourceMemberId = $interaction->member->id;
        $channel = $interaction->guild->channels->get('id', self::COVEN_CHANNEL_ID);
        $member = $interaction->guild->members->get('id', $memberId);
        if ($channel && $member) {
            $channel->setPermissions($member, [], [], 'Moderator removed access.');

            $context = [
                'memberId' => $memberId,
                'sourceMemberId' => $sourceMemberId,
            ];
            $message = $this->spud->twig->render('user/remove_coven.twig', $context);
            $builder->setDescription($message);
        } else {
            $builder->setDescription('Discord member and/or channel is not yet cached, please try again later.');
        }

        $builder->respondTo($interaction, true);
    }

    public function getCommandName(): string
    {
        return 'remove';
    }
}
