<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use OutOfBoundsException;
use Spudbot\Model;
use Spudbot\Model\Guild;
use Spudbot\Repository;

class GuildRepository extends Repository
{
    public function findById(string $id): Guild
    {
        // TODO: Implement find() method.
        if(!$id){
            throw new OutOfBoundsException("Guild with id {$id} does not exist.");
        }

        $guild = new Guild('1', '2', Carbon::now(), Carbon::now());
        $guild->setId(1);
        $guild->setOutputThreadId('1');

        return $guild;
    }

    public function save(Guild|Model $model): bool
    {
        $model->setModifiedAt(Carbon::now());
        // TODO: implemented the save() method.
    }

    public function remove(Guild|Model $model): bool
    {
        // TODO: Implement remove() method.
    }

    public function findByPart(Part $part): Model
    {
        // TODO: Implement findByPart() method.
    }
}