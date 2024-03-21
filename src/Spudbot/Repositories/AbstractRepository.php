<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Repositories;

use Carbon\Carbon;
use Discord\Parts\Part;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JsonException;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Exception\InvalidApiResponseException;
use Spudbot\Helpers\Collection;
use Spudbot\Http\ApiService;
use Spudbot\Http\Endpoint;
use Spudbot\Http\Router;
use Spudbot\Model\AbstractModel;
use Spudbot\Model\Guild;

abstract class AbstractRepository
{
    protected array $endpoints = [];
    protected Router $router;

    protected array $endpointVars = [];

    public function __construct(protected Client $client)
    {
        $this->router = Router::create($this->endpoints);
    }

    abstract public function findWithPart(Part $part);

    /**
     * @param string $id
     * @return AbstractModel
     * @throws ApiException
     * @throws ApiRequestFailure
     * @throws InvalidApiResponseException
     */
    public function findById(string $id): AbstractModel
    {
        $endpoint = $this->router
            ->getEndpoint('get', '/:id')
            ->setVariable('id', $id)
            ->setDefaultMethod('get');
        $json = $this->call($endpoint);
        return $this->hydrate($json);
    }

    /**
     * @param Endpoint $endpoint
     * @param array $options
     * @return mixed
     * @throws ApiException
     * @throws ApiRequestFailure
     * @throws InvalidApiResponseException
     */
    public function call(Endpoint $endpoint, array $options = []): mixed
    {
        $endpoint->addVariables($this->endpointVars);
        return ApiService::new($this->client)
            ->handle($endpoint->getMethod(), $endpoint, $options);
    }

    abstract public function hydrate(array $fields);

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function findByDiscordId(string $discordId, ?string $discordGuildId = null): Collection
    {
        $queryParameters = [
            'discord_id' => $discordId,
        ];
        if (!empty($discordGuildId)) {
            $queryParameters['guild_discord_id'] = $discordGuildId;
        }
        return $this->find([
            'query' => $queryParameters,
        ]);
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function find(array $options = []): Collection
    {
        $endpoint = $this->router->getEndpoint('get')
            ->setDefaultMethod('get');
        $json = $this->call($endpoint, $options);
        $results = Collection::collect($json);
        $results->transform(function ($item) {
            return $this->hydrate($item);
        });

        return $results;
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function findByGuild(Guild $guild): Collection
    {
        return $this->find([
            'query' => [
                'guild' => $guild->getExternalId(),
            ],
        ]);
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function all(): Collection
    {
        $endpoint = $this->router->getEndpoint('all')
            ->setDefaultMethod('get');
        $json = $this->call($endpoint, []);

        $results = Collection::collect($json);
        $results->transform(function ($item) {
            return $this->hydrate($item);
        });

        return $results;
    }

    public function remove(AbstractModel $model): bool
    {
        if (!$model->getExternalId()) {
            throw new InvalidArgumentException("Model cannot be removed without an id set.");
        }
        $endpoint = $this->router->getEndpoint('delete')
            ->setDefaultMethod('delete')
            ->setVariable('id', $model->getExternalId());
        return $this->call($endpoint, []);
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function save(AbstractModel $model): AbstractModel
    {
        $now = Carbon::now();
        $model->setUpdatedAt($now);
        if (!$model->getExternalId()) {
            $model->setCreatedAt($now);
            $options = [
                'json' => $model->toCreateArray(),
            ];
            $endpoint = $this->router->getEndpoint('post')
                ->setDefaultMethod('post');
        } else {
            $options = [
                'json' => $model->toUpdateArray(),
            ];
            $endpoint = $this->router->getEndpoint('put')
                ->setDefaultMethod('put')
                ->setVariable('id', $model->getExternalId());
        }

        $this->call($endpoint, $options);

        return $model;
    }

    /**
     * @throws GuzzleException
     * @throws InvalidApiResponseException
     * @throws ApiException|ApiRequestFailure
     */
    protected function handleApiRequest(string $endpoint, string $method, array $options): mixed
    {
        $response = $this->client->request($method, $endpoint, $options);
        try {
            $parsedResponse = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
            if (!$parsedResponse || !isset($parsedResponse['status'])) {
                throw new InvalidApiResponseException("$method request to $endpoint returned invalid JSON.");
            }
            if ($method === 'delete') {
                return $parsedResponse['status'];
            }
        } catch (JsonException $exception) {
            throw new InvalidApiResponseException($exception->getMessage());
        }
        if (!$parsedResponse['status']) {
            throw new ApiRequestFailure("$method to $endpoint was unsuccessful.");
        }
        if (!isset($parsedResponse['data'])) {
            throw new ApiException("Unable to retrieve data from $method request to $endpoint.");
        }
        return $parsedResponse['data'];
    }
}
