<?php
declare(strict_types=1);

namespace Spudbot\Interface;

use Carbon\Carbon;

abstract class IModel
{
    private null|int|string $id;
    private Carbon $createdAt;
    private Carbon $modifiedAt;

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
    public function getModifiedAt(): Carbon
    {
        return $this->modifiedAt;
    }
    public function setCreatedAt(Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
    public function setModifiedAt(Carbon $modifiedAt): void
    {
        $this->modifiedAt = $modifiedAt;
    }
    public function getId(): null|int|string
    {
        return $this->id ?? null;
    }
    public function setId(null|string|int $id): void
    {
        $this->id = $id;
    }
}