<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Spudbot\Hydrator\Strategy\ModelStrategy;
use Spudbot\Hydrator\Strategy\UsesStrategy;

class Marketplace extends AbstractModel
{
    private const STATUS_ACTIVE = 'active';
    private const STATUS_LOCKED = 'locked';
    private const STATUS_ARCHIVED = 'archived';
    private const STATUS_TAKEN = 'taken';
    public string $discordId;
    #[UsesStrategy(new ModelStrategy(Member::class))]
    public Member $member;
    public string $name;
    public string $lastStatus;
    public ?string $tags;

    public static function makeStatus(\Discord\Parts\Thread\Thread $thread): string
    {
        $status = self::STATUS_ACTIVE;
        if ($thread->locked) {
            $status = self::STATUS_LOCKED;
        }
        if ($thread->archived) {
            $status = self::STATUS_ARCHIVED;
        }
        return $status;
    }

    public static function makeTags(\Discord\Parts\Thread\Thread $thread): string
    {
        if (!is_array($thread->applied_tags)) {
            return (string)$thread->applied_tags;
        }
        return implode(',', $thread->applied_tags);
    }

    public function taken(): void
    {
        $this->lastStatus = self::STATUS_TAKEN;
    }

    public function archived(): void
    {
        $this->lastStatus = self::STATUS_ARCHIVED;
    }

    public function locked(): void
    {
        $this->lastStatus = self::STATUS_LOCKED;
    }

    public function active(): void
    {
        $this->lastStatus = self::STATUS_ACTIVE;
    }
}
