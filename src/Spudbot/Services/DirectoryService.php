<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Model\Channel;
use Spudbot\Model\Directory;
use Spudbot\Repositories\DirectoryRepository;

class DirectoryService
{
    public function __construct(public DirectoryRepository $directoryRepository)
    {
    }

    public function findWithForumChannel(Channel $channel): ?Directory
    {
        try {
            return $this->directoryRepository->findByForumChannel($channel);
        } catch (OutOfBoundsException $exception) {
            return null;
        }
    }

    public function findOrCreateWithForumChannel(Channel $channel, $defaults = []): Directory
    {
        try {
            return $this->directoryRepository->findByForumChannel($channel);
        } catch (OutOfBoundsException $exception) {
            return $this->save(Directory::create($defaults));
        }
    }

    public function save(Directory $directory): Directory
    {
        return $this->directoryRepository->save($directory);
    }
}
