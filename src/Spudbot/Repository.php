<?php
declare(strict_types=1);

namespace Spudbot;

use Discord\Parts\Part;
use OutOfBoundsException;

abstract class Repository
{
    abstract public function findByPart(Part $part): Model;
    abstract public function findById(string $id): Model;
    abstract public function save(Model $model): bool;
    abstract public function remove(Model $model): bool;
}