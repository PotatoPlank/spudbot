<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\AbstractModel;

class Member extends AbstractModel
{
    private string $discordId;
    private Guild $guild;
    private int $totalComments = 0;
    private ?string $username = null;
    private ?Member $verifiedBy = null;

    public static function withDatabaseRow(array $row, ?Guild $guild = null): self
    {
        $member = new self();

        if (array_key_exists('m_id', $row)) {
            $member->setId($row['m_id']);
            $member->setDiscordId($row['m_discord_id']);
            $member->setGuild(Guild::withDatabaseRow($row));
            $member->setUsername($row['m_username']);
            $member->setTotalComments($row['m_total_comments']);
            $member->setVerifiedBy($row['m_verified_by']);
            $member->setCreatedAt(Carbon::parse($row['m_created_at']));
            $member->setModifiedAt(Carbon::parse($row['m_modified_at']));
        } else {
            if (!isset($guild)) {
                throw new \InvalidArgumentException('Guild is required when you\'re not using joins.');
            }
            $member->setId($row['id']);
            $member->setDiscordId($row['discord_id']);
            $member->setGuild($guild);
            $member->setTotalComments($row['total_comments']);
            $member->setUsername($row['username']);
            $member->setVerifiedBy($row['verified_by']);
            $member->setCreatedAt(Carbon::parse($row['created_at']));
            $member->setModifiedAt(Carbon::parse($row['modified_at']));
        }

        return $member;
    }

    public static function hydrateWithArray(array $row): self
    {
        $member = new self();

        $member->setId($row['external_id']);
        $member->setDiscordId($row['discord_id']);
        $member->setTotalComments($row['total_comments']);
        $member->setUsername($row['username']);
        $member->setVerifiedBy($row['verified_by'] ? self::hydrateWithArray($row['verified_by']) : null);
        $member->setCreatedAt(Carbon::parse($row['created_at']));
        $member->setModifiedAt(Carbon::parse($row['updated_at']));

        if (isset($row['guild'])) {
            $member->setGuild(Guild::hydrateWithArray($row['guild']));
        }

        return $member;
    }

    public static function getUsernameWithPart(\Discord\Parts\User\Member $member): string
    {
        return $member->nick ?? $member->displayname;
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

    public function hasMetCommentThreshold(): bool
    {
        return $this->totalComments >= $_ENV['MEMBER_COMMENT_THRESHOLD'];
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
}
