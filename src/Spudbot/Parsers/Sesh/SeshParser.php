<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Parsers\Sesh;

use Discord\Helpers\Collection;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\Repository\Guild\MemberRepository;
use Spudbot\Exception\InvalidSeshEmbed;

class SeshParser
{
    private SeshDetails $details;

    public function __construct()
    {
        $this->details = new SeshDetails();
    }

    public static function fromMessage(Message $message): SeshDetails
    {
        $parser = new self();

        if (!SeshValidator::isValidEmbed($message)) {
            throw new InvalidSeshEmbed("{$message->id} is not a valid sesh embed.");
        }

        return $parser->parse($message);
    }

    public function parse(Message $message): SeshDetails
    {
        $this->extractDetails($message);

        return $this->details;
    }

    protected function extractDetails(Message $message): void
    {
        $this->details->title = $message->embeds->first()->title;
        $this->details->link = $message->link;
        $this->details->id = $message->id;
        $fields = $message->embeds->first()->fields;
        $this->extractTimestamps($fields);
        $this->extractAttendees($fields, $message->guild->members);
    }

    protected function extractTimestamps(Collection $fields): void
    {
        $time = $fields->find(function ($item) {
            return $item->name === 'Time';
        });
        $this->details->seshTimeString = trim(explode('[[+]]', $time->value)[0]);
        $this->details->scheduledAt = SeshValidator::getTimestampFromSeshString($this->details->seshTimeString);
    }

    protected function extractAttendees(Collection $fields, MemberRepository $memberRepository): void
    {
        foreach ($fields as $field) {
            $fieldValue = trim($field->value);
            if ($field->inline === false || strlen($fieldValue) <= 5) {
                continue;
            }
            $eventStatus = SeshValidator::getCleanGroupName($field->name);
            if (!$this->details->members->offsetExists($eventStatus)) {
                $this->details->members->put($eventStatus, collect());
            }

            $fieldValue = SeshValidator::getCleanMembersFieldValue($fieldValue);
            $members = explode("\n", $fieldValue);
            if (empty($members)) {
                continue;
            }
            foreach ($members as $member) {
                $user = $this->fetchMemberByName(trim($member), $memberRepository);
                if ($user) {
                    $this->details->members->get($eventStatus)->push($user);
                }
            }
        }
    }

    protected function fetchMemberByName(string $name, MemberRepository $memberRepository): ?Member
    {
        $user = $memberRepository->get('nick', $name);
        if ($user !== null) {
            return $user;
        }
        $user = $memberRepository->get('displayname', $name);
        return $user ?? $memberRepository->find(function ($memberObject) use ($name) {
            return $memberObject->user->username === $name;
        });
    }
}
