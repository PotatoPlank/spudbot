<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Builder;

use DI\Attribute\Inject;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Option;

class OptionBuilder
{
    public const DEFAULT_DESCRIPTION = 'No available description.';
    #[Inject]
    protected Discord $discord;
    private bool $required = false;
    private int $type = Option::STRING;
    private array $options = [];


    public function __construct(
        private readonly string $name,
        private readonly string $description = self::DEFAULT_DESCRIPTION
    ) {
    }

    /**
     * @param array{
     *     name:string,
     *     description:string,
     *     required:boolean,
     *     type:int
     * } $options
     * @return static
     */
    public function fill(array $options): static
    {
        foreach ($options as $parameter => $value) {
            if ($parameter === 'name' || $parameter === 'description') {
                continue;
            }
            $this->{$parameter} = $value;
        }

        return $this;
    }

    public function asSubCommand(): static
    {
        return $this->setType(Option::SUB_COMMAND);
    }

    public function setType(int $type = Option::STRING): static
    {
        $this->type = $type;
        return $this;
    }

    public function asChannel(): static
    {
        return $this->setType(Option::CHANNEL);
    }

    public function asRole(): static
    {
        return $this->setType(Option::ROLE);
    }

    public function setOptions(array $options): static
    {
        $this->options = [];

        foreach ($options as $key => $option) {
            $this->addOption($key, $option);
        }

        return $this;
    }

    public function addOption(int|string $name, Option $option): static
    {
        $this->options[$name] = $option;
        return $this;
    }

    public function create(): Option
    {
        $option = (new Option($this->discord))
            ->setName($this->name)
            ->setDescription($this->description)
            ->setRequired($this->required)
            ->setType($this->type);
        if (!empty($this->options)) {
            foreach ($this->options as $opt) {
                $option->addOption($opt);
            }
        }
        return $option;
    }

    public function setRequired(bool $required = true): static
    {
        $this->required = $required;
        return $this;
    }
}
