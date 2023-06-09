<?php
declare(strict_types=1);

namespace Spudbot\Bindable\SubCommand;


use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\ISubCommand;
use Spudbot\Model\EventAttendance;
use Spudbot\Repository\SQL\MemberRepository;

class UserEventReputation extends ISubCommand
{
    protected string $subCommand = 'reputation';

    public function execute(?Interaction $interaction): void
    {
        /**
         * @var MemberRepository $repository
         */
        $repository = $this->spud->getMemberRepository();
        $builder = $this->spud->getSimpleResponseBuilder();
        $builder->setTitle("Event Attendance");
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
            $context = [
                'memberId' => $memberPart->id,
                'reputation' => $reputation,
                'eventsAttended' => $totalAttended,
                'eventsInterested' => $totalEvents,
            ];
            $message = $this->spud->getTwig()->render('user/event_reputation.twig', $context);

            $builder->setDescription($message);
        } else {
            $builder->setDescription("<@{$memberPart->id}> hasn't attended an event yet.");
        }
        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}