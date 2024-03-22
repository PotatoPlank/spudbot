<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Parsers\Sesh;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SeshDetails
{
    public Carbon $scheduledAt;
    public string $seshTimeString;
    public ?string $title = null;
    public string $link;
    public string $id;
    public Collection $members;

    public function __construct()
    {
        $this->members = collect();
    }
}
