<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;

class Thread extends IModel
{
    private string $discordId;
    private Guild $guild;

    public static function withDatabaseRow(array $row, ?Guild $guild = null): self
    {
        $thread = new self();

        if(array_key_exists('t_id', $row)){
            $thread->setId($row['t_id']);
            $thread->setDiscordId($row['t_discord_id']);
            $thread->setGuild(Guild::withDatabaseRow($row));
            $thread->setCreatedAt(Carbon::parse($row['t_created_at']));
            $thread->setModifiedAt(Carbon::parse($row['t_modified_at']));
        }else{
            if(!isset($guild)){
                throw new \InvalidArgumentException('Guild is required when you\'re not using joins.');
            }
            $thread->setId($row['t_id']);
            $thread->setDiscordId($row['t_discord_id']);
            $thread->setGuild($guild);
            $thread->setCreatedAt(Carbon::parse($row['t_created_at']));
            $thread->setModifiedAt(Carbon::parse($row['t_modified_at']));
        }

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