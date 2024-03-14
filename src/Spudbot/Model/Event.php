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
    private Guild $guild;
    private ?string $channelId;
    private string $name;
    private EventType $type;
    private ?string $seshId;
    private ?string $nativeId;
    private Carbon $scheduledAt;

    public function toCreateArray(): array
    {
        return [
            'guild' => $this->getGuild()->getId(),
            'type' => $this->getType(),
            'sesh_id' => $this->getSeshId(),
            'native_id' => $this->getNativeId(),
            'discord_channel_id' => $this->getChannelId(),
            'name' => $this->getName(),
            'scheduled_at' => $this->getScheduledAt()->toIso8601String(),
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

    public function setType(EventType $type): void
    {
        $this->type = $type;
    }

    public function getSeshId(): ?string
    {
        return $this->seshId ?? null;
    }

    public function setSeshId(?string $id): void
    {
        $this->seshId = $id;
    }

    public function getNativeId(): ?string
    {
        return $this->nativeId ?? null;
    }

    public function setNativeId(?string $id): void
    {
        $this->nativeId = $id;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(?string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getScheduledAt(): Carbon
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(Carbon $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
    }

    public function toUpdateArray(): array
    {
        return [
            'discord_channel_id' => $this->getChannelId(),
            'name' => $this->getName(),
            'scheduled_at' => $this->getScheduledAt()->toIso8601String(),
        ];
    }
}
