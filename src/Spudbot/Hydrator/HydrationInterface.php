<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */
declare(strict_types=1);

namespace Spudbot\Hydrator;

/**
 * @template T
 */
interface HydrationInterface
{
    /**
     * Hydrate $object with the provided $data.
     *
     * @param array $data
     * @param object<T> $object
     * @return object<T>
     */
    public function hydrate(array $data, object $object);
}
