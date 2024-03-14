<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Repositories;

use Discord\Parts\Part;
use OutOfBoundsException;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Helpers\Collection;
use Spudbot\Model\AbstractModel;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;

///**
// * @method EventAttendance findById(string $id)
// * @method EventAttendance save(EventAttendance $model)
// * @method bool remove(EventAttendance $model)
// */
class EventAttendanceRepository extends AbstractRepository
{
    protected array $endpoints = [
        'default' => 'events/:eventId/attendance',
        'post' => 'post|events/:eventId',
        'put' => 'put|events/:eventId/:attendanceId',
        'delete' => 'delete|events/:eventId/:attendanceId',
        'getByEvent' => 'events/:eventId/attendance',
        'getByMember' => 'members/:memberId/attendance',
    ];
    protected array $endpointVars = [
        'attendanceId' => null,
        'eventId' => null,
        'memberId' => null,
    ];

    /**
     * @throws MethodNotImplementedException
     */
    public function findWithPart(Part $part): EventAttendance
    {
        throw new MethodNotImplementedException();
    }

    /**
     * @throws MethodNotImplementedException
     */
    public function findById(string $id): AbstractModel
    {
        throw new MethodNotImplementedException();
    }

    /**
     * @throws MethodNotImplementedException
     */
    public function findByDiscordId(string $discordId, ?string $discordGuildId = null): Collection
    {
        throw new MethodNotImplementedException();
    }

    /**
     * @throws MethodNotImplementedException
     */
    public function findByGuild(Guild $guild): Collection
    {
        throw new MethodNotImplementedException();
    }

    public function save(EventAttendance|AbstractModel $model): AbstractModel
    {
        $this->endpointVars = [
            'attendanceId' => $model->getId(),
            'eventId' => $model->getEvent()->getId(),
        ];
        return parent::save($model);
    }

    public function remove(EventAttendance|AbstractModel $model): bool
    {
        $this->endpointVars = [
            'attendanceId' => $model->getId(),
            'eventId' => $model->getEvent()->getId(),
        ];
        return parent::remove($model);
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function getMemberAttendance(Member $member): Collection
    {
        $endpoint = $this->router
            ->getEndpoint('getByMember')
            ->setVariable('memberId', $member->getId());
        $json = $this->call($endpoint);
        $results = Collection::collect($json);
        $results->transform(function ($item) {
            return $this->hydrate($item);
        });
        return $results;
    }

    public function hydrate(array $fields): EventAttendance
    {
        return EventAttendance::create([
            'id' => $fields['external_id'],
            'status' => $fields['status'],
            'noShowStatus' => $fields['no_show'],
            'member' => Member::create($fields['member']),
            'event' => Event::create($fields['event']),
            'createdAt' => $fields['created_at'],
            'modifiedAt' => $fields['updated_at'],
        ]);
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function getMembersEventAttendance(Member $member, Event $event): EventAttendance
    {
        $attendees = $this->getEventAttendance($event);
        $attendees->filter(function (EventAttendance $attendance) use ($member) {
            return $attendance->getMember()->getId() === $member->getId();
        });

        if ($attendees->empty()) {
            throw new OutOfBoundsException("Event data associated with specified user and event does not exist.");
        }

        return $attendees->first();
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function getEventAttendance(Event $event): Collection
    {
        $endpoint = $this->router
            ->getEndpoint('getByEvent')
            ->setVariable('eventId', $event->getId());

        $json = $this->call($endpoint);
        $results = Collection::collect($json);
        $results->transform(function ($item) {
            return $this->hydrate($item);
        });

        return $results;
    }
}