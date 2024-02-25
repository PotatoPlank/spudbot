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
use Spudbot\Repository\SQL\MemberRepository;

class TotalUserComments extends AbstractSubCommandSubscriber
{

    public function getCommandName(): string
    {
        return 'total_comments';
    }

    public function update(?Interaction $interaction = null): void
    {
        /**
         * @var MemberRepository $repository
         */
        $repository = $this->spud->memberRepository;
        $builder = $this->spud->getSimpleResponseBuilder();
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);

        $member = $repository->findByPart($memberPart);

        $context = [
            'memberId' => $memberPart->user->id,
            'totalComments' => $member->getTotalComments(),
        ];

        $builder->setTitle("{$memberPart->user->displayname} Comment Count");
        $builder->setDescription($this->spud->twig->render('user/comment_count.twig', $context));

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}
