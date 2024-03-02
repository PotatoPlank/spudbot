<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Carbon\Carbon;

abstract class AbstractModel
{
    private null|int|string $id;
    private Carbon $createdAt;
    private Carbon $modifiedAt;

    /**
     * @param array $fields
     * @return static
     */
    public static function create(array $fields = []): static
    {
        $self = new static();
        if (!isset($fields['createdAt'])) {
            $self->createdAt = Carbon::now();
        }
        if (!isset($fields['modifiedAt'])) {
            $self->modifiedAt = Carbon::now();
        }

        foreach ($fields as $field => $value) {
            $mutator = $self->getFieldMutator($field);
            $self->$mutator = $value;
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

    public function getModifiedAt(): Carbon
    {
        return $this->modifiedAt;
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
