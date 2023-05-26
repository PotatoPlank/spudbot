<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Discord\Parts\Part;
use Spudbot\Collection;
use Spudbot\Model;
use Spudbot\Model\Member;
use Spudbot\Repository;

class MemberRepository extends SQLRepository
{

    public function findById(string $id): Model
    {
        // TODO: Implement find() method.
    }

    public function save(Member|Model $model): bool
    {
        // TODO: Implement save() method.
    }

    public function remove(Member|Model $model): bool
    {
        // TODO: Implement remove() method.
    }

    public function findByPart(Part $part): Model
    {
        // TODO: Implement findByPart() method.
    }

    public function getAll(): Collection
    {
        // TODO: Implement getAll() method.
    }
}