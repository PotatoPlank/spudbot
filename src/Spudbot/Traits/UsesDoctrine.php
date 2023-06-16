<?php

namespace Spudbot\Traits;

use Doctrine\DBAL\Connection;

trait UsesDoctrine
{
    public function __construct(protected Connection $dbal) {}
}