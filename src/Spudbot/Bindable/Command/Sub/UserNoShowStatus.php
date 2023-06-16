<?php

namespace Spudbot\Bindable\Command\Sub;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Repository\SQL\EventRepository;
use Spudbot\Repository\SQL\MemberRepository;

class UserNoShowStatus extends SubCommand
{
    protected string $subCommand = 'no_show';
    public function execute(?Interaction $interaction): void
    {
        /**
         * @var EventRepository $eventRepository
         * @var MemberRepository $memberRepository
         */
        $eventRepository = $this->spud->getEventRepository();
        $memberRepository = $this->spud->getMemberRepository();
        $builder = $this->spud->getSimpleResponseBuilder();
        $title = 'Event No Show';
        $message = '';

        $userId = $this->options['user']->value;
        $eventId = $this->options['internal_id']->value;
        $noShowStatus = $this->options['status']->value ?? true;
        $memberPart = $interaction->guild->members->get('id', $userId);

        $member = $memberRepository->findByPart($memberPart);

        try{
            $event = $eventRepository->findById($eventId);
            $eventAttendance = $eventRepository->getAttendanceByMemberAndEvent($member, $event);

            $eventAttendance->wasNoShow($noShowStatus);

            /**
             * TODO: Save event attendance with repository
             */

            $message = "<@{$member->getDiscordId()}>'s status was updated.";
        }catch(\Exception $exception){
            $message = 'An event with that id and user could not be found.';
        }

        $builder->setTitle($title);
        $builder->setDescription($message);
        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}