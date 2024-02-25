<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

class SpudOptions
{
    private array $options;

    public function __construct(string $token)
    {
        $this->setToken($token);
    }

    public function setToken(string $token): void
    {
        $this->options['token'] = $token;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setIntents(array|int $intents): void
    {
        $this->options['intents'] = $intents;
    }

    public function shouldLoadAllMembers($boolean = true): void
    {
        $this->options['loadAllMembers'] = $boolean;
    }

    public function setRawOption(string $key, string $value): void
    {
        $this->options[$key] = $value;
    }
}
