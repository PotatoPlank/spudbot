<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Builder;

use DI\Attribute\Inject;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;

class CommandBuilder
{
    public const DEFAULT_DESCRIPTION = 'No available description.';
    #[Inject]
    protected Discord $discord;
    private int|string $permission;
    private array $options = [];

    public function __construct(
        private readonly string $name,
        private readonly string $description = self::DEFAULT_DESCRIPTION
    ) {
    }

    public function setDefaultPermissions(int|string $permission): static
    {
        $this->permission = $permission;
        return $this;
    }

    public function setOptions(array $options): static
    {
        $this->options = [];

        foreach ($options as $key => $option) {
            $this->addOption($key, $option);
        }

        return $this;
    }

    public function addOption(string $name, Option $option): static
    {
        $this->options[$name] = $option;
        return $this;
    }

    public function create(): Command
    {
        return new Command($this->discord, $this->command()->toArray());
    }

    public function command(): \Discord\Builders\CommandBuilder
    {
        $builder = \Discord\Builders\CommandBuilder::new()
            ->setDescription($this->description)
            ->setName($this->name);
        foreach ($this->options as $option) {
            $builder->addOption($option);
        }
        if (isset($this->permission)) {
            $builder->setDefaultMemberPermissions($this->permission);
        }
        return $builder;
    }
}
