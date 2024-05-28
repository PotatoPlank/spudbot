<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator\Strategy;

use Carbon\Carbon;
use DateTime;

class CarbonStrategy implements StrategyInterface
{
    private $defaultHydrateFunc = null;
    private $defaultExtractFunc = null;

    public function __construct(
        private $dateFormat = DateTime::ATOM,
        ?callable $defaultHydrateFunc = null,
        ?callable $defaultExtractFunc = null
    ) {
        $this->defaultHydrateFunc = $defaultHydrateFunc;
        $this->defaultExtractFunc = $defaultExtractFunc;
    }

    public function extract(mixed $value, ?object $context = null): mixed
    {
        return $value instanceof Carbon ? $value->format($this->dateFormat) : $this->extractDefault($value);
    }

    protected function extractDefault(mixed $value): mixed
    {
        $func = $this->defaultExtractFunc;
        return $this->defaultExtractFunc === null ? $value : $func($value);
    }

    public function hydrate(mixed $value, ?array $data = null): mixed
    {
        return $value instanceof Carbon ? $this->hydrateDefault($value) : Carbon::parse($value);
    }

    protected function hydrateDefault(mixed $value): mixed
    {
        $func = $this->defaultHydrateFunc;
        return $this->defaultHydrateFunc === null ? $value : $func($value);
    }
}
