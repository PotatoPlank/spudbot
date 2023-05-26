<?php

namespace Spudbot\Repository\SQL;

use Doctrine\DBAL\Connection;
use Spudbot\Repository;

abstract class SQLRepository extends Repository
{
    public function __construct(protected Connection $dbal){}
}