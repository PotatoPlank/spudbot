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
    private null|int|string $id;
    private Carbon $createdAt;
    private Carbon $updatedAt;

    /**
     * @param array $fields
     * @return static
     */
    public static function create(array $fields = []): static
    {
        $self = new static();
        if (!isset($fields['createdAt']) && !isset($fields['created_at'])) {
            $fields['createdAt'] = Carbon::now();
        }
        if (!isset($fields['updatedAt']) && !isset($fields['updated_at'])) {
            $fields['updatedAt'] = Carbon::now();
        }

        foreach ($fields as $field => $value) {
            $mutator = $self->getFieldMutator($field);
            if (strrpos($mutator, 'At') === strlen($mutator) - 1) {
                $value = Carbon::parse($value);
            }
            $self->$mutator($value);
        }

        return $self;
    }

    protected function getFieldMutator(string $field): string
    {
        if (str_starts_with($field, 'set')) {
            return $field;
        }
        return $this->isSnakeCase($field) ? $this->snakeToCamelCase($field) : 'set' . ucfirst($field);
    }

    protected function isSnakeCase(string $field): bool
    {
        return !str_starts_with($field, '_');
    }

    protected function snakeToCamelCase(string $field): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', "set_$field"))));
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

    abstract public function toCreateArray(): array;

    abstract public function toUpdateArray(): array;
}
