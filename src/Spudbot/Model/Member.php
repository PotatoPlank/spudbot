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

    public static function withDatabaseRow(array $row, ?Guild $guild = null): self
    {
        $member = new self();

        if(array_key_exists('m_id', $row)){
            $member->setId($row['m_id']);
            $member->setDiscordId($row['m_discord_id']);
            $member->setGuild(Guild::withDatabaseRow($row));
            $member->setTotalComments($row['m_total_comments']);
            $member->setCreatedAt(Carbon::parse($row['m_created_at']));
            $member->setModifiedAt(Carbon::parse($row['m_modified_at']));
        }else{
            if(!isset($guild)){
                throw new \InvalidArgumentException('Guild is required when you\'re not using joins.');
            }
            $member->setId($row['id']);
            $member->setDiscordId($row['discord_id']);
            $member->setGuild($guild);
            $member->setTotalComments($row['total_comments']);
            $member->setCreatedAt(Carbon::parse($row['created_at']));
            $member->setModifiedAt(Carbon::parse($row['modified_at']));
        }

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

    public function hasMetCommentThreshold(): bool
    {
        return $this->totalComments >= $_ENV['MEMBER_COMMENT_THRESHOLD'];
    }

}