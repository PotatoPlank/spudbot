<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;

abstract class AbstractModel
{
    protected Carbon $createdAt;
    protected Carbon $updatedAt;
    protected null|int|string $id;

    /**
     * @param array $fields
     * @return static
     */
    public static function create(array $fields = []): static
    {
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function setCreatedAt(Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(Carbon $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getExternalId(): null|int|string
    {
        return $this->id ?? null;
    }

    public function setExternalId(null|string|int $id): void
    {
        $this->id = $id;
    }
}
