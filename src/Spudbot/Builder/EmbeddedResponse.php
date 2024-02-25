<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Builder;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;

class EmbeddedResponse
{
    private string $title;
    private string $description;
    private array $allowedMentions = [];
    private array $options = [];

    public function __construct(public Discord $discord)
    {
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getEmbeddedMessage(): MessageBuilder
    {
        $builder = MessageBuilder::new();
        $options = $this->options;

        $options['title'] = $this->title;
        if (!empty($this->description)) {
            $options['description'] = $this->description;
        }
        if (!empty($this->allowedMentions)) {
            $builder->setAllowedMentions($this->allowedMentions);
        }

        $embed = $this->discord->factory(Embed::class, $options);

        return $builder->setEmbeds([
            $embed
        ]);
    }

    public function setAllowedMentions(array $allowedMentions): void
    {
        $this->allowedMentions = $allowedMentions;
    }
}
