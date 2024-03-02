<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */


use Spudbot\Services\ChannelService;
use Spudbot\Services\EventAttendanceService;
use Spudbot\Services\EventService;
use Spudbot\Services\GuildService;
use Spudbot\Services\MemberService;

return [
    ChannelService::class => DI\autowire(),
    MemberService::class => DI\autowire(),
    GuildService::class => DI\autowire(),
    EventService::class => DI\autowire(),
    EventAttendanceService::class => DI\autowire(),
];
