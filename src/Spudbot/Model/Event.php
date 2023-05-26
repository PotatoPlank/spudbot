<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Model;

class Event extends Model
{
    private string $discordId;
    private string $channelId;
    private string $name;
    private string $type;
    private string $seshId;
    private string $nativeId;

    private Carbon $scheduledAt;
}