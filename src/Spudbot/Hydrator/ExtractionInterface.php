<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */
declare(strict_types=1);

namespace Spudbot\Hydrator;


interface ExtractionInterface
{
    /**
     * Extract values from $object.
     * @param object $object
     * @return array
     */
    public function extract(object $object, ?array $filter = []): array;
}
