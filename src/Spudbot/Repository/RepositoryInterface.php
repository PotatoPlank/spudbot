<?php

namespace Spudbot\Repository;

interface RepositoryInterface
{
    public function find(string $id);
    public function save(): bool;
    public function remove(): bool;
}