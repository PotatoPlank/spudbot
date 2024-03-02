<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\AbstractModel;

class Directory extends AbstractModel
{
    private Channel $directoryChannel;
    private Channel $forumChannel;
    private string $embedId;

    public static function withDatabaseRow(
        array $row,
        Channel $directoryChannel,
        Channel $forumChannel
    ): self {
        $channel = new self();

        $channel->setId($row['id']);
        $channel->setDirectoryChannel($directoryChannel);
        $channel->setForumChannel($forumChannel);
        $channel->setEmbedId($row['embed_id']);
        $channel->setCreatedAt(Carbon::parse($row['created_at']));
        $channel->setModifiedAt(Carbon::parse($row['modified_at']));

        return $channel;
    }

    public static function hydrateWithArray(array $row): self
    {
        $directory = new self();

        $directory->setId($row['external_id']);
        $directory->setEmbedId($row['embed_id']);
        $directory->setCreatedAt(Carbon::parse($row['created_at']));
        $directory->setModifiedAt(Carbon::parse($row['updated_at']));

        if ($row['directory_channel']) {
            $directory->setDirectoryChannel($row['directory_channel']);
        }
        if ($row['forum_channel']) {
            $directory->setForumChannel($row['forum_channel']);
        }

        return $directory;
    }

    /**
     * @return Channel
     */
    public function getDirectoryChannel(): Channel
    {
        return $this->directoryChannel;
    }

    /**
     * @param Channel $directoryChannel
     */
    public function setDirectoryChannel(Channel $directoryChannel): void
    {
        $this->directoryChannel = $directoryChannel;
    }

    /**
     * @return Channel
     */
    public function getForumChannel(): Channel
    {
        return $this->forumChannel;
    }

    /**
     * @param Channel $forumChannel
     */
    public function setForumChannel(Channel $forumChannel): void
    {
        $this->forumChannel = $forumChannel;
    }

    /**
     * @return string
     */
    public function getEmbedId(): string
    {
        return $this->embedId;
    }

    /**
     * @param string $embedId
     */
    public function setEmbedId(string $embedId): void
    {
        $this->embedId = $embedId;
    }

    public function getTitle(\Discord\Parts\Channel\Channel $channel): string
    {
        return $channel->name . ' thread directory';
    }

}
