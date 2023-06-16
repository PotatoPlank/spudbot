<?php

namespace Spudbot\Integration;

use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Spudbot\Collection;
use Spudbot\Exception\InvalidSeshEmbed;

class SeshEmbedParser
{
    private ?Carbon $scheduledAt;
    private string $seshTimeString;
    private ?string $title;
    private string $link;

    private Collection $members;
    public function __construct(private Message $message){
        $this->members = new Collection();
        $isSesh = $this->message->components->count() > 0 && $this->message->user_id === '616754792965865495';
        if(!$isSesh){
            throw new InvalidSeshEmbed("{$this->message->id} is not a valid sesh embed.");
        }
        $isEvent = str_contains($this->message->components[0]->components[0]->custom_id, 'event_rsvp');
        if(!$isEvent){
            throw new InvalidSeshEmbed("{$this->message->id} is not a valid sesh embed.");
        }

        $fields = $this->message->embeds[0]->fields;
        $this->title = $this->message->embeds[0]->title;
        $this->seshTimeString = trim(explode('[[+]]', $fields['Time']->value)[0]);
        $this->scheduledAt = $this->parseTimestampFromSeshString($this->seshTimeString);
        $this->link = $this->message->link;

        unset($fields['Time'], $fields['Duration'], $fields['Repeat']);

        foreach($fields as $field)
        {
            $fieldValue = trim($field->value);
            $eventStatus = trim(preg_replace('/\s\(\d(.*?)\)/', '', $field->name));

            $isFieldLongEnoughToIndicateAMemberList = strlen($fieldValue) > 5;
            if($isFieldLongEnoughToIndicateAMemberList) {
                if (str_starts_with($fieldValue, '>>> ')){
                    $fieldValue = substr($fieldValue, 4);
                }
                $members = explode("\n", $fieldValue);
                if (!empty($members)){
                    foreach ($members as $member) {
                        $member = trim($member);
                        $user = $message->guild->members->get('nick', $member);
                        if(!$user)
                        {
                            $user = $message->guild->members->find(function ($memberObject) use ($member){
                                return $memberObject->user->username === $member;
                            });
                        }
                        if($user)
                        {
                            if(!isset($this->members[$eventStatus])){
                                $this->members->set($eventStatus, new Collection());
                            }
                            $this->members->get($eventStatus)->push($user->user);
                        }
                    }
                }
            }
        }
    }

    private function parseTimestampFromSeshString(string $seshTimestampString): ?Carbon
    {
        if(!preg_match('/<t:(.*?):F>/', $seshTimestampString, $match))
        {
            return null;
        }

        return Carbon::createFromTimestamp($match[1]);
    }

    /**
     * @return Carbon|null
     */
    public function getScheduledAt(): ?Carbon
    {
        return $this->scheduledAt;
    }

    /**
     * @return string
     */
    public function getSeshTimeString(): string
    {
        return $this->seshTimeString;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return Collection
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }
}