<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

class Member extends AbstractModel
{
    private string $discordId;
    private Guild $guild;
    private int $totalComments = 0;
    private ?string $username = null;
    private ?Member $verifiedBy = null;

    public static function getUsernameWithPart(\Discord\Parts\User\Member $member): string
    {
        return $member->nick ?? $member->displayname;
    }

    public function hasMetCommentThreshold(): bool
    {
        return $this->totalComments >= $_ENV['MEMBER_COMMENT_THRESHOLD'];
    }

    public function toCreateArray(): array
    {
        return [
            'discord_id' => $this->getDiscordId(),
            'guild' => $this->getGuild()->getExternalId(),
            'total_comments' => $this->getTotalComments(),
            'username' => $this->getUsername(),
            'verified_by_member' => $this->getVerifiedBy()?->getExternalId(),
        ];
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }

    public function getGuild(): Guild
    {
        return $this->guild;
    }

    public function setGuild(Guild $guild): void
    {
        $this->guild = $guild;
    }

    public function getTotalComments(): int
    {
        return $this->totalComments;
    }

    public function setTotalComments(int $totalComments): void
    {
        $this->totalComments = $totalComments;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return Member|null
     */
    public function getVerifiedBy(): ?Member
    {
        return $this->verifiedBy;
    }

    /**
     * @param Member|null $verifiedBy
     */
    public function setVerifiedBy(?Member $verifiedBy): void
    {
        $this->verifiedBy = $verifiedBy;
    }

    public function toUpdateArray(): array
    {
        return [
            'total_comments' => $this->getTotalComments(),
            'username' => $this->getUsername(),
            'verified_by_member' => $this->getVerifiedBy()?->getExternalId(),
        ];
    }
}
