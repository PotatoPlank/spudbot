<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;

class Thread extends IModel
{
    private string $discordId;
    private Guild $guild;

    public static function withDatabaseRow(array $row, Guild $guild): self
    {
        $thread = new self();

        $thread->setId($row['t_id'] ?? $row['id']);
        $thread->setDiscordId($row['t_discord_id'] ?? $row['discord_id']);
        $thread->setGuild($guild);
        $thread->setCreatedAt(Carbon::parse($row['t_created_at'] ?? $row['created_at']));
        $thread->setModifiedAt(Carbon::parse($row['t_modified_at'] ?? $row['modified_at']));

        return $thread;
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
}