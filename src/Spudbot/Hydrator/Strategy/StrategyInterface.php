<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator\Strategy;

interface StrategyInterface
{
    /**
     * @param mixed $value The original value
     * @param object|null $context The original object for context
     * @return mixed Returns the value that should be extracted.
     */
    public function extract(mixed $value, ?object $context = null): mixed;

    /**
     * @param mixed $value The original value
     * @param array|null $data The original data for context
     * @return mixed Returns the value that should be used for hydration.
     */
    public function hydrate(mixed $value, ?array $data = null): mixed;
}
