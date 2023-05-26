<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Model;

class Guild extends Model
{
    private string $discordId;
    private ?string $outputChannelId;
    private ?string $outputThreadId;

    public function setDiscordId(string $discordId){
        $this->discordId = $discordId;
    }

    public function setOutputChannelId(?string $channelId){
        $this->outputChannelId = $channelId;
    }
    public function setOutputThreadId(?string $threadId){
        $this->outputThreadId = $threadId;
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function getOutputChannelId(): ?string
    {
        return $this->outputChannelId;
    }

    public function getOutputThreadId(): ?string
    {
        return $this->outputThreadId;
    }
}