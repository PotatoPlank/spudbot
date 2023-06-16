<?php

namespace Spudbot\Bindable\Command\Sub;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Repository\SQL\MemberRepository;

class TotalUserComments extends SubCommand
{
    protected string $subCommand = 'total_comments';
    public function execute(?Interaction $interaction): void
    {
        /**
         * @var MemberRepository $repository
         */
        $repository = $this->spud->getMemberRepository();
        $builder = $this->spud->getSimpleResponseBuilder();
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);

        $member = $repository->findByPart($memberPart);

        $builder->setTitle("{$memberPart->user->displayname} Comment Count");
        $builder->setDescription("<@{$memberPart->user->id}> posted {$member->getTotalComments()} times.");

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}