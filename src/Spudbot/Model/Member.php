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

}