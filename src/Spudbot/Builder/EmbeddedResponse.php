<?php

namespace Spudbot\Builder;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;

class EmbeddedResponse
{
    private string $title;
    private string $description;
    private array $options = [];
    public function __construct(public Discord $discord) {}

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getEmbeddedMessage(): MessageBuilder
    {
        $builder = MessageBuilder::new();
        $options = $this->options;

        $options['title'] = $this->title;
        if(!empty($this->description)){
            $options['description'] = $this->description;
        }

        $embed = $this->discord->factory(Embed::class, $options);

        return $builder->setEmbeds([
            $embed
        ]);
    }
}