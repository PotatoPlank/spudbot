<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Model;

class Guild extends Model
{
    private ?string $outputThreadId;

    public function __construct(
        private string $discordId,
        private string $outputChannelId,
        Carbon $createdAt,
        Carbon $modifiedAt
    ) {
        parent::__construct($createdAt, $modifiedAt);
    }

    public function setOutputThreadId(?string $outputThreadId){
        $this->outputThreadId = $outputThreadId;
    }
}