<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

class Directory extends AbstractModel
{
    private Channel $directoryChannel;
    private Channel $forumChannel;
    private string $embedId;

    public function getTitle(\Discord\Parts\Channel\Channel $channel): string
    {
        return $channel->name . ' thread directory';
    }

    public function toCreateArray(): array
    {
        return [
            'embed_id' => $this->getEmbedId(),
            'directory_channel' => $this->getDirectoryChannel()->getExternalId(),
            'forum_channel' => $this->getForumChannel()->getExternalId(),
        ];
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

    public function toUpdateArray(): array
    {
        return [
            'embed_id' => $this->getEmbedId(),
            'directory_channel' => $this->getDirectoryChannel()->getExternalId(),
            'forum_channel' => $this->getForumChannel()->getExternalId(),
        ];
    }
}
