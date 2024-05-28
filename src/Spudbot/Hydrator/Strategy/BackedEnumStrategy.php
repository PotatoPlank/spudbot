<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator\Strategy;

class BackedEnumStrategy implements StrategyInterface
{
    /**
     * @param class-string $enumClass
     */
    public function __construct(private readonly string $enumClass)
    {
    }

    public function extract(mixed $value, ?object $context = null): mixed
    {
        if (!$value instanceof $this->enumClass) {
            return $value;
        }
        return $value->value;
    }


    public function hydrate(mixed $value, ?array $data = null): mixed
    {
        if ($value instanceof $this->enumClass) {
            return $value;
        }
        return $this->enumClass::from($value);
    }
}
