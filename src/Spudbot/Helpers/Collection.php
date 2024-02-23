<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Helpers;

use Traversable;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private array $collection = [];

    public function set(string|int $key, mixed $value): void
    {
        $this->collection[$key] = $value;
    }

    public function first()
    {
        if (empty($this->collection)) {
            return null;
        }

        return current($this->collection);
    }

    public function get(string|int $key): mixed
    {
        return $this->collection[$key];
    }

    public function getAll(): array
    {
        return $this->collection;
    }

    public function push(mixed $value): void
    {
        $this->collection[] = $value;
    }

    public function clear(): void
    {
        $this->collection = [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->collection[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->collection[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->collection[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->collection[$offset]);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function empty(): bool
    {
        return empty($this->collection);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->collection);
    }

    public function filter(callable $callback): void
    {
        $this->collection = array_filter($this->collection, $callback, ARRAY_FILTER_USE_BOTH);
    }

    public function transform(callable $callback): void
    {
        foreach ($this->collection as $key => $item) {
            $this->collection[$key] = $callback($item, $key);
        }
    }

    public function has(callable $callback): bool
    {
        foreach ($this->collection as $key => $item) {
            $result = $callback($item, $key);
            if ($result === true) {
                return true;
            }
        }
        return false;
    }

}
