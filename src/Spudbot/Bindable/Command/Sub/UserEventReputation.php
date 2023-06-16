<?php

namespace Spudbot\Bindable\Command\Sub;


use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\EventAttendance;
use Spudbot\Repository\SQL\MemberRepository;

class UserEventReputation extends SubCommand
{
    protected string $subCommand = 'reputation';

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
        $eventsAttended = $repository->getEventAttendance($member);
        $totalEvents = count($eventsAttended);
        $totalAttended = 0;
        if($totalEvents > 0)
        {
            /**
             * @var EventAttendance $event
             */
            foreach ($eventsAttended as $event)
            {
                if($event->getEvent()->getScheduledAt()->gt(Carbon::now()))
                {
                    $totalEvents--;
                }else if(!$event->getNoShowStatus()){
                    $totalAttended++;
                }
            }

            $reputation = round(($totalAttended / $totalEvents) * 100);
            $message = "<@{$memberPart->id}> has as an event reputation of {$reputation}%." . PHP_EOL;
            $message .= "They have attended {$totalAttended}/{$totalEvents} events they have expressed interest in.";
            $builder->setTitle("Event Attendance");
            $builder->setDescription($message);

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        } else {
            $builder->setTitle("Event Attendance");
            $builder->setDescription("<@{$memberPart->id}> hasn't attended an event yet.");

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        }
    }
}