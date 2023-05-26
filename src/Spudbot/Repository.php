<?php
declare(strict_types=1);

namespace Spudbot;

use Discord\Parts\Part;
use Doctrine\DBAL\Connection;

abstract class Repository
{
    public function __construct(protected Connection $dbal){}
    abstract public  function getAll(): Collection;
    abstract public function findByPart(Part $part): Model;
    abstract public function findById(string $id): Model;
    abstract public function save(Model $model): bool;
    abstract public function remove(Model $model): bool;
}