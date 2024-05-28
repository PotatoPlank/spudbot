<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */
declare(strict_types=1);

namespace Spudbot\Hydrator\Strategy;

interface StrategyEnabledInterface
{
    /**
     *
     * @param string $name The name of the strategy to register.
     * @param StrategyInterface $strategy The strategy to register.
     */
    public function addStrategy(string $name, StrategyInterface $strategy): void;

    /**
     *
     * @param string $name The name of the strategy to get.
     * @return StrategyInterface
     */
    public function getStrategy(string $name): StrategyInterface;

    /**
     *
     * @param string $name The name of the strategy to check for.
     * @return bool
     */
    public function hasStrategy(string $name): bool;

    /**
     *
     * @param string $name The name of the strategy to remove.
     */
    public function removeStrategy(string $name): void;
}
