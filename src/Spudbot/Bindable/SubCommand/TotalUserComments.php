<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Bindable\SubCommand;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\ISubCommand;
use Spudbot\Repository\SQL\MemberRepository;

class TotalUserComments extends ISubCommand
{
    protected string $subCommand = 'total_comments';

    public function execute(?Interaction $interaction): void
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
