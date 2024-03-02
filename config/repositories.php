<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Spudbot\Repository\Api\ChannelRepository;
use Spudbot\Repository\Api\DirectoryRepository;
use Spudbot\Repository\Api\EventRepository;
use Spudbot\Repository\Api\GuildRepository;
use Spudbot\Repository\Api\MemberRepository;
use Spudbot\Repository\Api\ReminderRepository;
use Spudbot\Repository\Api\ThreadRepository;

return [
    MemberRepository::class => DI\autowire(),
    EventRepository::class => DI\autowire(),
    GuildRepository::class => DI\autowire(),
    ThreadRepository::class => DI\autowire(),
    ChannelRepository::class => DI\autowire(),
    ReminderRepository::class => DI\autowire(),
    DirectoryRepository::class => DI\autowire(),
];
