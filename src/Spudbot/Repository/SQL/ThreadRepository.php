<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Discord\Parts\Part;
use Spudbot\Model;
use Spudbot\Model\Thread;
use Spudbot\Repository;

class ThreadRepository extends Repository
{

    public function findById(string $id): Model
    {
        // TODO: Implement find() method.
    }

    public function save(Thread|Model $model): bool
    {
        // TODO: Implement save() method.
    }

    public function remove(Thread|Model $model): bool
    {
        // TODO: Implement remove() method.
    }

    public function findByPart(Part $part): Model
    {
        // TODO: Implement findByPart() method.
    }
}