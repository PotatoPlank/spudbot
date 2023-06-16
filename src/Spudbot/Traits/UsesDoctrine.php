<?php

namespace Spudbot\Traits;

use Doctrine\DBAL\Connection;
use Spudbot\Collection;

trait UsesDoctrine
{
    public function __construct(protected Connection $dbal) {}
}