<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Model;

class Member extends Model
{
    private string $discordId;
    private Guild $guild;
    private int $totalComments;

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