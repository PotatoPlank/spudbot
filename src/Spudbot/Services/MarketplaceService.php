<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use Discord\Parts\Thread\Thread;
use InvalidArgumentException;
use OutOfBoundsException;
use Spudbot\Model\Marketplace;
use Spudbot\Repositories\MarketplaceRepository;

class MarketplaceService
{
    public function __construct(
        public MarketplaceRepository $marketplaceRepository,
        public MemberService $memberService
    ) {
    }

    public function findOrCreateWithPart(Thread $thread): Marketplace
    {
        try {
            $model = $this->marketplaceRepository->findWithPart($thread);
            if ($model) {
                return $model;
            }
            throw new OutOfBoundsException('Does not exist.');
        } catch (OutOfBoundsException $exception) {
            if (!$thread->owner_member) {
                throw new InvalidArgumentException('Null owner member provided.');
            }
            return $this->save($this->marketplaceRepository->new([
                'discord_id' => $thread->id,
                'member' => $this->memberService->findOrCreateWithPart($thread->owner_member),
                'name' => $thread->name,
                'last_status' => Marketplace::makeStatus($thread),
                'tags' => Marketplace::makeTags($thread),
            ]));
        }
    }

    public function findWithPart(Thread $thread): ?Marketplace
    {
        if ($thread->owner_member === null) {
            try {
                return $this->marketplaceRepository->findByDiscordId($thread->id)->first();
            } catch (OutOfBoundsException $exception) {
                return null;
            }
        }

        return $this->findOrCreateWithPart($thread);
    }

    public function save(Marketplace $marketplace): Marketplace
    {
        return $this->marketplaceRepository->save($marketplace);
    }

    public function remove(Marketplace $marketplace): bool
    {
        return $this->marketplaceRepository->remove($marketplace);
    }
}
