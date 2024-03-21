<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Http;

use BadMethodCallException;

class Router
{
    private array $defaultRoutes = [
        'default',
    ];

    public function __construct(private array $definitions = [])
    {
    }

    public function setDefinitions(array $definitions): self
    {
        $this->definitions = $definitions;
        return $this;
    }

    public function addDefinition(string $routeName, string $method, string $endpoint): self
    {
        $this->definitions[$routeName] = "$method|$endpoint";
        return $this;
    }

    public function getEndpoint(string $routeName, string $suffix = ''): Endpoint
    {
        $definition = $this->getRoute($routeName);
        return Endpoint::create($definition['route'] . $suffix)
            ->setMethod($definition['method']);
    }

    public function getRoute(string $name): array
    {
        $options = [$name, ...$this->defaultRoutes,];

        foreach ($options as $option) {
            if (isset($this->definitions[$option])) {
                $ruleDefinition = $this->definitions[$option];
                if (str_contains($ruleDefinition, '|')) {
                    [$method, $route] = explode('|', $ruleDefinition);
                    return [
                        'method' => $method,
                        'route' => $route,
                    ];
                }
                return [
                    'method' => null,
                    'route' => $this->definitions[$option],
                ];
            }
        }
        throw new BadMethodCallException("$name does not exist as a valid endpoint.");
    }

    public static function create(array $definitions = []): self
    {
        return new self($definitions);
    }
}
