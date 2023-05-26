<?php

namespace Spudbot\Repository;

use Doctrine\DBAL\Connection;
use Spudbot\Repository;

abstract class SQLRepository extends Repository
{
    public function __construct(protected Connection $dbal){}
}