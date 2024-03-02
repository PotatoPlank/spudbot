<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Spudbot\Repositories\ChannelRepository;
use Spudbot\Repositories\DirectoryRepository;
use Spudbot\Repositories\EventRepository;
use Spudbot\Repositories\GuildRepository;
use Spudbot\Repositories\MemberRepository;
use Spudbot\Repositories\ReminderRepository;
use Spudbot\Repositories\ThreadRepository;

return [
    MemberRepository::class => DI\autowire(),
    EventRepository::class => DI\autowire(),
    GuildRepository::class => DI\autowire(),
    ThreadRepository::class => DI\autowire(),
    ChannelRepository::class => DI\autowire(),
    ReminderRepository::class => DI\autowire(),
    DirectoryRepository::class => DI\autowire(),
];
