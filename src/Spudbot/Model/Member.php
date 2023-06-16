<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;

class Member extends IModel
{
    private string $discordId;
    private Guild $guild;
    private int $totalComments;

    public static function withDatabaseRow(array $row, Guild $guild): self
    {
        $member = new self();

        $member->setId($row['m_id'] ?? $row['id']);
        $member->setDiscordId($row['m_discord_id'] ?? $row['discord_id']);
        $member->setGuild($guild);
        $member->setTotalComments($row['m_total_comments'] ?? $row['total_comments']);
        $member->setCreatedAt($row['m_created_at'] ?? Carbon::parse($row['created_at']));
        $member->setModifiedAt($row['m_modified_at'] ?? Carbon::parse($row['modified_at']));

        return $member;
    }

    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function setGuild(Guild $guild): void
    {
        $this->guild = $guild;
    }

    public function getGuild(): Guild
    {
        return $this->guild;
    }

    public function setTotalComments(int $totalComments): void
    {
        $this->totalComments = $totalComments;
    }

    public function getTotalComments(): int
    {
        return $this->totalComments;
    }

}