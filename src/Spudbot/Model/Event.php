<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;

class Event extends IModel
{
    private Guild $guild;
    private ?string $channelId;
    private string $name;
    private \Spudbot\Type\EventType $type;
    private ?string $seshId;
    private ?string $nativeId;
    private Carbon $scheduledAt;

    public static function withDatabaseRow(array $row, Guild $guild): self
    {
        $event = new self();

        $event->setId($row['id']);
        $event->setGuild($guild);
        $event->setChannelId($row['channel_id']);
        $event->setName($row['name']);
        $event->setType(\Spudbot\Type\EventType::from($row['type']));
        $event->setSeshId($row['sesh_id']);
        $event->setNativeId($row['native_id']);
        $event->setScheduledAt(Carbon::parse($row['scheduled_at']));
        $event->setCreatedAt(Carbon::parse($row['created_at']));
        $event->setModifiedAt(Carbon::parse($row['modified_at']));

        return $event;
    }

    public function setGuild(Guild $guild): void
    {
        $this->guild = $guild;
    }

    public function getGuild(): Guild
    {
        return $this->guild;
    }


    public function setChannelId(?string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setType(\Spudbot\Type\EventType $type): void
    {
        $this->type = $type;
    }

    public function getType(): \Spudbot\Type\EventType
    {
        return $this->type;
    }

    public function setSeshId(?string $id): void
    {
        $this->seshId = $id;
    }

    public function getSeshId(): ?string
    {
        return $this->seshId;
    }

    public function setNativeId(?string $id): void
    {
        $this->nativeId = $id;
    }

    public function getNativeId(): ?string
    {
        return $this->nativeId;
    }

    public function setScheduledAt(Carbon $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
    }

    public function getScheduledAt(): Carbon
    {
        return $this->scheduledAt;
    }
}