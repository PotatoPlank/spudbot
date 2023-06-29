<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Spudbot\Helpers\Collection;
use Spudbot\Model\Channel;
use Spudbot\Model\Directory;
use Spudbot\Model\Guild;

abstract class IDirectoryRepository
{
    abstract public function findById(string|int $id): Directory;

    abstract public function findByGuild(Guild $guild): Collection;

    abstract public function findByForumChannel(Channel $channel): Directory;

    abstract public function findByDirectoryChannel(Channel $channel): Collection;

    abstract public function getEmbedContentFromPart(\Discord\Parts\Channel\Channel $forumChannel): string;

    abstract public function getAll(): Collection;


    abstract public function save(Directory $directory): bool;

    abstract public function remove(Directory $directory): bool;


}