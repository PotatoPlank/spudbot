<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Interface;

use Spudbot\Helpers\Collection;

abstract class AbstractObserver
{
    protected Collection $listeners;

    public function __construct()
    {
        $this->listeners = new Collection();
    }

    public function subscribe(mixed $name, mixed $listener): void
    {
        if (!$this->listeners->offsetExists($name)) {
            $this->listeners->set($name, new Collection());
        }
        $this->listeners->get($name)->push($listener);
    }

    public function all(): Collection
    {
        return $this->listeners;
    }

    public function destroy(mixed $name): void
    {
        $this->listeners->offsetUnset($name);
    }

    public function emit(mixed $name, ...$args): void
    {
        $this->listeners->get($name)->forEach(function (callable $listener) use ($args) {
            $listener($args);
        });
    }
}
