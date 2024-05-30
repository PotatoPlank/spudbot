<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Discord\Parts\Part;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Model\Marketplace;

/**
 * @method Marketplace findById(string $id)
 * @method Marketplace save(Marketplace $model)
 * @method bool remove(Marketplace $model)
 */
class MarketplaceRepository extends AbstractRepository
{
    protected string $model = Marketplace::class;
    protected array $updateFilter = [
        'member',
        'discord_id',
    ];

    protected array $endpoints = [
        'default' => 'marketplaces',
        'put' => 'put|marketplaces/:id',
        'delete' => 'delete|marketplaces/:id',
    ];

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function findWithPart(Part $part): ?Marketplace
    {
        return $this->findByDiscordId($part->id)->first();
    }
}
