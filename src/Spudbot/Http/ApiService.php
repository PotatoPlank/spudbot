<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Http;


use GuzzleHttp\Client;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Exception\InvalidApiResponseException;

class ApiService
{
    public function __construct(private Client $client)
    {
    }

    public static function new(Client $client): self
    {
        return new self($client);
    }

    public function handle(string $method, string $endpoint, array $options): mixed
    {
        $response = $this->client->request($method, $endpoint, $options);
        $content = $this->getParsedBody($response);
        $success = $this->wasSuccessful($content);
        if ($method === 'delete') {
            return $success;
        }
        if (!$success) {
            throw new ApiRequestFailure("$method to $endpoint was unsuccessful.");
        }
        if (!isset($content['data'])) {
            throw new ApiException("Unable to retrieve data from $method request to $endpoint.");
        }
        return $content['data'];
    }

    protected function getParsedBody(ResponseInterface $response): mixed
    {
        try {
            $parsedResponse = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
            if (!$parsedResponse) {
                throw new InvalidApiResponseException();
            }
            return $parsedResponse;
        } catch (JsonException $exception) {
            throw new InvalidApiResponseException($exception->getMessage());
        }
    }

    protected function wasSuccessful(array $parsedResponse): bool
    {
        if (!isset($parsedResponse['success'])) {
            throw new InvalidApiResponseException();
        }
        return (bool)$parsedResponse['success'];
    }
}
