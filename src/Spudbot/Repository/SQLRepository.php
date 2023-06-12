<?php

namespace Spudbot\Repository;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Spudbot\Collection;
use Spudbot\Repository;

abstract class SQLRepository extends Repository
{
    private Collection $cacheCollection;
    private ?Carbon $timeout;
    public function __construct(protected Connection $dbal){
        $this->cacheCollection = new Collection();
    }

    public function setCache(mixed $key, mixed $value): void
    {
        if(empty($this->timeout)){
            $this->timeout = Carbon::now()->addMinutes(2);
        }elseif($this->isCacheExpired()){
            $this->clearCache();
        }

        $this->cacheCollection->set($key, $value);
    }

    public function getCache(mixed $key): mixed
    {
        if($this->isCacheExpired()){
            $this->clearCache();
        }
        if(!isset($this->cacheCollection[$key]))
        {
            throw new \OutOfBoundsException("Key $key does not exist in the cache.");
        }
        return $this->cacheCollection->get($key);
    }

    public function isCached(mixed $key): bool
    {
        if($this->isCacheExpired()){
            $this->clearCache();
        }
        return isset($this->cacheCollection[$key]);
    }

    public function clearCache(): void
    {
        $this->cacheCollection->clear();
        $this->timeout = null;
    }

    public function isCacheExpired(): bool
    {
        return !empty($this->timeout) && $this->timeout->lessThanOrEqualTo(Carbon::now());
    }
}