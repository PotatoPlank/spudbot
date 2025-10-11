<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator;

use Doctrine\Inflector\InflectorFactory;
use InvalidArgumentException;
use Spudbot\Hydrator\Strategy\StrategyEnabledInterface;
use Spudbot\Hydrator\Strategy\StrategyInterface;

abstract class AbstractHydrator implements HydratorInterface, StrategyEnabledInterface
{
    protected array $strategies = [];
    protected array $fieldMap = [];

    public function addStrategy(string $name, StrategyInterface $strategy): void
    {
        $this->strategies[$name] = $strategy;
    }

    public function removeStrategy(string $name): void
    {
        unset($this->strategies[$name]);
    }

    public function extractValue(string $name, mixed $value, ?object $object = null)
    {
        return $this->hasStrategy($name) ? $this->getStrategy($name)->extract($value, $object) : $value;
    }

    public function hasStrategy(string $name): bool
    {
        return isset($this->strategies[$name]) || isset($this->strategies['*']);
    }

    public function getStrategy(string $name): Strategy\StrategyInterface
    {
        if (isset($this->strategies[$name])) {
            return $this->strategies[$name];
        }
        if (!isset($this->strategies['*'])) {
            throw new InvalidArgumentException("No strategy $name or wildcard strategy specified.");
        }
        return $this->strategies['*'];
    }

    public function hydrateValue(string $name, mixed $value, ?array $data = null)
    {
        return $this->hasStrategy($name) ? $this->getStrategy($name)->hydrate($value, $data) : $value;
    }

    public function extractName(string $name, ?object $object = null): string
    {
        $name = InflectorFactory::create()->build()
            ->tableize($name);
        $nameOverride = array_search($name, $this->fieldMap, true);
        if ($nameOverride !== false) {
            return $nameOverride;
        }
        return $name;
    }

    public function hydrateName(string $name, ?array $data = null): string
    {
        return $this->fieldMap[$name] ?? InflectorFactory::create()->build()
            ->camelize($name);
    }
}
