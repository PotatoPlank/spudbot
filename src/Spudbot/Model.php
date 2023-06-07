<?php
declare(strict_types=1);

namespace Spudbot;

use Carbon\Carbon;

abstract class Model
{
    private ?int $id;
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
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}