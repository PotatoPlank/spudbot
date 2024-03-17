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
use Spudbot\Services\MemberService;

class TotalUserComments extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected MemberService $memberService;

    public function getCommandName(): string
    {
        return 'total_comments';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $builder = $this->spud->interact();
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);
        if (!$memberPart) {
            $builder->error("Unable to find member $userId")
                ->respondTo($interaction);
            return;
        }

        $member = $this->memberService->findOrCreateWithPart($memberPart);

        $context = [
            'memberId' => $memberPart->user->id,
            'totalComments' => $member->getTotalComments(),
        ];

        $builder->setTitle("{$memberPart->user->displayname} Comment Count")
            ->setDescription($this->spud->twig->render('user/comment_count.twig', $context))
            ->respondTo($interaction);
    }
}
