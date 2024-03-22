<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use BadMethodCallException;
use Discord\Parts\Part;
use Spudbot\Model\Channel;
use Spudbot\Model\Directory;

/**
 * @method Directory findById(string $id)
 * @method Directory save(Directory $model)
 * @method bool remove(Directory $model)
 */
class DirectoryRepository extends AbstractRepository
{
    protected array $endpoints = [
        'default' => 'directories',
        'put' => 'put|directories/:id',
        'delete' => 'delete|directories/:id',
    ];

    public function findByForumChannel(Channel $channel): Directory
    {
        $response = $this->find([
            'query' => [
                'forum_channel' => $channel->getExternalId(),
            ],
        ]);

        return $response->first();
    }

    public function findWithPart(Part $part): void
    {
        throw new BadMethodCallException('Directories cannot be located by part.');
    }

    public function hydrate(array $fields): Directory
    {
        return Directory::create([
            'external_id' => $fields['external_id'],
            'embed_id' => $fields['embed_id'],
            'directory_channel' => $fields['directory_channel'],
            'forum_channel' => $fields['forum_channel'],
            'created_at' => $fields['created_at'],
            'updated_at' => $fields['updated_at'],
        ]);
    }
}
