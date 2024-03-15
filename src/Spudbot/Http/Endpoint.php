<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Http;

class Endpoint
{
    private array $variables = [];
    private ?string $method = null;
    private string $defaultMethod = '';

    public function __construct(private readonly string $endpoint)
    {
    }

    public static function create(string $endpoint): self
    {
        return new self($endpoint);
    }

    public function addVariables(array $variables): self
    {
        $this->variables = array_merge($this->variables, $variables);
        return $this;
    }

    public function setVariable(string $name, string $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->getEndpoint();
    }

    public function getEndpoint(): string
    {
        $endpoint = $this->endpoint;
        preg_match_all('~:\w+~', $this->endpoint, $matches);
        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (empty($match)) {
                    continue;
                }
                $varName = str_ireplace(':', '', $match);
                if (!isset($this->variables[$varName])) {
                    continue;
                }
                $endpoint = str_replace($match, $this->variables[$varName], $match);
            }
        }

        return $endpoint;
    }

    public function getMethod(): string
    {
        return $this->method ?? $this->defaultMethod;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function setDefaultMethod(string $defaultMethod): self
    {
        $this->defaultMethod = $defaultMethod;
        return $this;
    }
}
