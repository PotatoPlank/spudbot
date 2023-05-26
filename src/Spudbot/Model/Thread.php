<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Model;

class Thread extends Model
{
    private string $discordId;
    private Guild $guild;
}