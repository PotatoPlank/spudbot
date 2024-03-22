<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Types\EventType;

class Event extends AbstractModel
{
    protected array $dates = [
        'scheduled_at'
    ];
    private Guild $guild;
    private ?string $discordChannelId;
    private string $name;
    private EventType $type;
    private ?string $seshMessageId;
    private ?string $nativeEventId;
    private ?Carbon $scheduledAt;

    public function toCreateArray(): array
    {
        return [
            'guild' => $this->getGuild()->getExternalId(),
            'type' => $this->getType()->value,
            'sesh_id' => $this->getSeshMessageId(),
            'native_id' => $this->getNativeEventId(),
            'discord_channel_id' => $this->getDiscordChannelId(),
            'name' => $this->getName(),
            'scheduled_at' => $this->getScheduledAt()?->toIso8601String(),
        ];
    }

    public function getGuild(): Guild
    {
        return $this->guild;
    }

    public function setGuild(Guild $guild): void
    {
        $this->guild = $guild;
    }

    public function getType(): EventType
    {
        return $this->type;
    }

    public function setType(string|EventType $type): void
    {
        $this->type = $type instanceof EventType ? $type : EventType::from($type);
    }

    public function getSeshMessageId(): ?string
    {
        return $this->seshMessageId ?? null;
    }

    public function setSeshMessageId(?string $id): void
    {
        $this->seshMessageId = $id;
    }

    public function getNativeEventId(): ?string
    {
        return $this->nativeEventId ?? null;
    }

    public function setNativeEventId(?string $id): void
    {
        $this->nativeEventId = $id;
    }

    public function getDiscordChannelId(): string
    {
        return $this->discordChannelId;
    }

    public function setDiscordChannelId(?string $discordChannelId): void
    {
        $this->discordChannelId = $discordChannelId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getScheduledAt(): ?Carbon
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?Carbon $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
    }

    public function toUpdateArray(): array
    {
        return [
            'discord_channel_id' => $this->getDiscordChannelId(),
            'name' => $this->getName(),
            'scheduled_at' => $this->getScheduledAt()?->toIso8601String(),
        ];
    }
}
