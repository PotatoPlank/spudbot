<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;
use Spudbot\Types\EventType;

class
Event extends IModel
{
    private Guild $guild;
    private ?string $channelId;
    private string $name;
    private EventType $type;
    private ?string $seshId;
    private ?string $nativeId;
    private Carbon $scheduledAt;

    public static function withDatabaseRow(array $row, ?Guild $guild = null): self
    {
        $event = new self();

        if(array_key_exists('e_id', $row)){
            $event->setId($row['e_id']);
            $event->setGuild(Guild::withDatabaseRow($row));
            $event->setChannelId($row['e_channel_id']);
            $event->setName($row['e_name']);
            $event->setType(EventType::from($row['e_type']));
            $event->setSeshId($row['e_sesh_id']);
            $event->setNativeId($row['e_native_id']);
            $event->setScheduledAt(Carbon::parse($row['e_scheduled_at']));
            $event->setCreatedAt(Carbon::parse($row['e_created_at']));
            $event->setModifiedAt(Carbon::parse($row['e_modified_at']));
        }else{
            if(!isset($guild)){
                throw new \InvalidArgumentException('Guild is required when you\'re not using joins.');
            }
            $event->setId($row['id']);
            $event->setGuild($guild);
            $event->setChannelId($row['channel_id']);
            $event->setName($row['name']);
            $event->setType(EventType::from($row['type']));
            $event->setSeshId($row['sesh_id']);
            $event->setNativeId($row['native_id']);
            $event->setScheduledAt(Carbon::parse($row['scheduled_at']));
            $event->setCreatedAt(Carbon::parse($row['created_at']));
            $event->setModifiedAt(Carbon::parse($row['modified_at']));
        }

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

    public function setType(EventType $type): void
    {
        $this->type = $type;
    }

    public function getType(): EventType
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