<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Util\Str;

abstract class AbstractModel
{
    protected array $dates = [];
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
        if (!isset($fields['created_at'])) {
            $fields['created_at'] = Carbon::now();
        }
        if (!isset($fields['updated_at'])) {
            $fields['updated_at'] = Carbon::now();
        }

        foreach ($fields as $field => $value) {
            $self->mutate($field, $value);
        }

        return $self;
    }

    /**
     * @throws \ReflectionException
     */
    public function mutate(string $field, mixed $value): void
    {
        $mutator = $this->getFieldMutator($field);
        if (!method_exists($this, $mutator)) {
            $this->$field = $value;
            return;
        }
        $fieldType = $this->getMutatorParameterType($mutator, $value);
        $value = $this->castValue($fieldType, $fieldType, $value);
        $this->$mutator($value);
    }

    protected function getFieldMutator(string $field): string
    {
        if (str_starts_with($field, 'set')) {
            return $field;
        }
        return Str::isSnakeCase($field) ? Str::snakeToCamelCase("set_$field") : 'set' . ucfirst($field);
    }

    private function getMutatorParameterType(string $mutator, mixed $value): string
    {
        $valueType = gettype($value);
        $reflection = new \ReflectionParameter([$this, $mutator], 0);
        if (!$reflection->hasType()) {
            return $valueType;
        }
        $fieldType = $reflection->getType();
        if ($fieldType instanceof \ReflectionUnionType) {
            $acceptedTypes = $fieldType->getTypes();
            return in_array($valueType, $acceptedTypes, true) ? $valueType : $acceptedTypes[0]->getName();
        }

        return $fieldType->getName();
    }

    protected function castValue(?string $type, string $fieldName, mixed $value): mixed
    {
        if ($type === null) {
            return $value;
        }
        $dateFields = [...$this->dates, 'updated_at', 'created_at'];
        array_walk($dateFields, static function (&$val) {
            $val = Str::camelToSnakeCase($val);
        });
        if (!empty($value) && ($type === Carbon::class || in_array($fieldName, $dateFields, true))) {
            $value = Carbon::parse($value);
        }
        if ($type === 'int') {
            $value = (int)$value;
        }
        if ($type === 'string') {
            $value = (string)$value;
        }
        if (is_array($value) && is_subclass_of($type, self::class)) {
            $value = $type::create($value);
        }
        return $value;
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
