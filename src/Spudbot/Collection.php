<?php

namespace Spudbot;

use Traversable;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private array $collection = [];

    public function set(string|int $key, mixed $value): void
    {
        $this->collection[$key] = $value;
    }

    public function get(string|int $key): mixed
    {
        return $this->collection[$key];
    }

    public function getAll(): array
    {
        return $this->collection;
    }

    public function push(mixed $value)
    {
        $this->collection[] = $value;
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

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->collection);
    }
}