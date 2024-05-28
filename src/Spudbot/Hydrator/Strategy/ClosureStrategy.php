<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator\Strategy;

class ClosureStrategy implements StrategyInterface
{
    protected $extractFunction;
    protected $hydrateFunction;

    public function __construct(?callable $extractFunction = null, ?callable $hydrateFunction = null)
    {
        $this->extractFunction = $extractFunction;
        $this->hydrateFunction = $hydrateFunction;
    }

    public function extract(mixed $value, ?object $context = null): mixed
    {
        $func = $this->extractFunction;
        return $this->extractFunction ? $func($value, $context) : $value;
    }

    public function hydrate(mixed $value, ?array $data = null): mixed
    {
        $func = $this->hydrateFunction;
        return $this->hydrateFunction ? $func($value, $data) : $value;
    }
}
