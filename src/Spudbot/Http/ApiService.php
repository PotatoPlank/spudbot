<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Http;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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

    /**
     * @throws ApiRequestFailure
     * @throws InvalidApiResponseException
     * @throws ApiException
     */
    public function handle(string $method, string $endpoint, array $options): mixed
    {
        $params = json_encode($options);
        $requestDescription = "$method request to $endpoint options: $params";

        try {
            $response = $this->client->request($method, $endpoint, $options);
        } catch (GuzzleException $exception) {
            $message = $exception->getMessage();
            throw new ApiException(
                message: "Unable to process $requestDescription error: $message",
                previous: $exception
            );
        }
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            if ($statusCode >= 500) {
                throw new InternalServiceError(
                    message: "Internal Server Error: $requestDescription",
                    statusCode: $statusCode
                );
            }
            $validation = json_encode((string)$response->getBody());
            throw new UnprocessableEntity(
                message: "400 Error: $requestDescription, error: $validation",
                statusCode: $statusCode
            );
        }
        if ($method === 'delete') {
            return $statusCode === 204;
        }
        $content = $this->getParsedBody($response);
        //$success = $this->wasSuccessful($content);

        if (isset($content['errors'])) {
            if (isset($content['message'])) {
                throw new ApiRequestFailure($content['message']);
            }
            throw new ApiException("$method to $endpoint was unsuccessful.");
        }
        if (!isset($content['data'])) {
            throw new ApiException("Unable to retrieve data from $method request to $endpoint.");
        }
        return $content['data'];
    }

    /**
     * @throws InvalidApiResponseException
     */
    protected function getParsedBody(ResponseInterface $response): mixed
    {
        try {
            $parsedResponse = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
            if (!$parsedResponse) {
                throw new InvalidApiResponseException($response->getBody()->__toString());
            }
            return $parsedResponse;
        } catch (JsonException $exception) {
            throw new InvalidApiResponseException($exception->getMessage());
        }
    }

    protected function wasSuccessful(array $parsedResponse): bool
    {
        if (!isset($parsedResponse['status'])) {
            throw new InvalidApiResponseException("Error: " . json_encode($parsedResponse));
        }
        return (bool)$parsedResponse['status'];
    }
}
