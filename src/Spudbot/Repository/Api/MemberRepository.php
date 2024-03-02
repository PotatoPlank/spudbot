<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repository\Api;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IMemberRepository;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use Spudbot\Traits\UsesApi;

class MemberRepository extends IMemberRepository
{
    use UsesApi;

    public function findById(string|int $id): Member
    {
        $response = $this->client->get("members/{$id}");
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Member with id {$id} does not exist.");
        }

        return Member::hydrateWithArray($json);
    }

    public function findByPart(\Discord\Parts\User\Member $member): Member
    {
        return $this->findByDiscordId($member->id, $member->guild->id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId): Member
    {
        $response = $this->client->get('members', [
            'query' => [
                'discord_id' => $discordId,
                'guild_discord_id' => $discordGuildId,
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (empty($json['data'])) {
            throw new OutOfBoundsException("Member with id {$discordId} does not exist.");
        }


        return Member::hydrateWithArray($json['data'][0]);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $response = $this->client->get('members', [
            'query' => [
                'guild' => $guild->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json['data'])) {
            foreach ($json['data'] as $row) {
                $member = Member::hydrateWithArray($row);

                $collection->push($member);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $response = $this->client->get('members');
        $json = $this->getResponseJson($response);

        if (!empty($json['data'])) {
            foreach ($json['data'] as $row) {
                $member = Member::hydrateWithArray($row);

                $collection->push($member);
            }
        }

        return $collection;
    }

    public function getEventReputation(Collection $eventsAttended)
    {
        $totalEvents = count($eventsAttended);
        $totalAttended = 0;
        if ($totalEvents > 0) {
            /**
             * @var EventAttendance $event
             */
            foreach ($eventsAttended as $event) {
                if ($event->getEvent()->getScheduledAt()->gt(Carbon::now())) {
                    $totalEvents--;
                } elseif (!$event->getNoShowStatus()) {
                    $totalAttended++;
                }
            }

            return round(($totalAttended / $totalEvents) * 100);
        }
        return 0;
    }

    public function getEventAttendance(Member $member): Collection
    {
        $collection = new Collection();

        $response = $this->client->get("members/{$member->getId()}/attendance");
        $json = $this->getResponseJson($response);

        if (!empty($json['data'])) {
            foreach ($json['data'] as $row) {
                $attendance = EventAttendance::hydrateWithArray($row);

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    public function getTopCommentersByGuild(Guild $guild, $limit = 10): Collection
    {
        $collection = new Collection();
        $response = $this->client->get('members', [
            'query' => [
                'sort' => 'total_comments',
                'direction' => 'desc',
                'guild_discord_id' => $guild->getDiscordId(),
                'limit' => $limit,
            ],
        ]);

        $json = $this->getResponseJson($response);

        if (!empty($json['data'])) {
            foreach ($json['data'] as $row) {
                $member = Member::hydrateWithArray($row);

                $collection->push($member);
            }
        }

        return $collection;
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     */
    public function save(Member $member): Member
    {
        $member->setModifiedAt(Carbon::now());

        $params = [
            'total_comments' => $member->getTotalComments(),
            'username' => $member->getUsername(),
            'verified_by_member' => $member->getVerifiedBy(),
        ];

        if (!$member->getId()) {
            $member->setCreatedAt(Carbon::now());
            $params = [
                'discord_id' => $member->getDiscordId(),
                'guild' => $member->getGuild()->getId(),
                ...$params,
            ];

            $response = $this->client->post("members", [
                'json' => $params,
            ]);
        } else {
            $response = $this->client->put("members/{$member->getId()}", [
                'json' => $params,
            ]);
        }

        if (!$this->wasSuccessful($response)) {
            throw new ApiException();
        }

        return $member;
    }

    public function remove(Member $member): bool
    {
        if (!$member->getId()) {
            throw new OutOfBoundsException("Member is unable to be removed without a proper id.");
        }

        $response = $this->client->delete("members/{$member->getId()}");
        $json = $this->getResponseJson($response);
        if (!$json['success']) {
            throw new \RuntimeException("Removing member {$member->getId()} was unsuccessful");
        }

        return true;
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     */
    public function saveMemberEventAttendance(EventAttendance $eventAttendance): EventAttendance
    {
        $eventAttendance->setModifiedAt(Carbon::now());

        $params = [
            'status' => $eventAttendance->getStatus(),
            'no_show' => $eventAttendance->getNoShowStatus(),
        ];

        if (!$eventAttendance->getId()) {
            $eventAttendance->setCreatedAt(Carbon::now());

            $params = [
                ...$params,
                'member' => $eventAttendance->getMember()->getId(),
            ];

            $response = $this->client->post("events/{$eventAttendance->getEvent()->getId()}", [
                'json' => $params,
            ]);
        } else {
            $response = $this->client->put(
                "events/{$eventAttendance->getEvent()->getId()}/{$eventAttendance->getId()}",
                [
                    'json' => $params,
                ]
            );
        }

        if (!$this->wasSuccessful($response)) {
            throw new ApiException();
        }

        return $eventAttendance;
    }

}
