<?php
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