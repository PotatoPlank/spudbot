<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator\Strategy;

use Spudbot\Hydrator\AbstractHydrator;
use Spudbot\Hydrator\EntityHydrator;

class ModelStrategy implements StrategyInterface
{

    public function __construct(private readonly string $class, private ?AbstractHydrator $hydrator = null)
    {
        $this->hydrator ??= new EntityHydrator();
    }

    public function extract(mixed $value, ?object $context = null): mixed
    {
        if (!$value instanceof $this->class) {
            return $value;
        }
        return $value->getExternalId();
    }


    public function hydrate(mixed $value, ?array $data = null): mixed
    {
        if (empty($value)) {
            return null;
        }
        $class = $this->class;
        $obj = new $class();
        return $this->hydrator->hydrate($value, $obj);
    }
}
