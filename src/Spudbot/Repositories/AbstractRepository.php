<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */
declare(strict_types=1);

namespace Spudbot\Repositories;

use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Parts\Part;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Exception\InvalidApiResponseException;
use Spudbot\Helpers\Collection;
use Spudbot\Http\ApiService;
use Spudbot\Http\Endpoint;
use Spudbot\Http\Router;
use Spudbot\Hydrator\EntityHydrator;
use Spudbot\Model\AbstractModel;
use Spudbot\Model\Guild;

/**
 * @template T
 */
abstract class AbstractRepository
{
    protected static array $storage = [];
    #[Inject]
    public EntityHydrator $hydrator;
    protected array $endpoints = [];
    protected Router $router;
    protected array $endpointVars = [];
    protected array $excluded = [
        'external_id',
        'created_at',
        'updated_at',
    ];
    protected array $createFilter = [];
    protected array $updateFilter = [];
    /**
     * @var class-string<T> $model
     */
    protected string $model;

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
        if (isset(self::$storage[$id])) {
            return self::$storage[$id];
        }
        $endpoint = $this->router
            ->getEndpoint('get', '/:id')
            ->setVariable('id', $id)
            ->setDefaultMethod('get');
        $json = $this->call($endpoint);
        $model = $this->hydrate($json);
        self::$storage[$id] = $model;
        return $model;
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
            ->handle($endpoint->getMethod(), (string)$endpoint, $options);
    }

    public function new(array $fields = [])
    {
        $fields = [
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            ...$fields,
        ];
        $model = $this->getModel();
        $this->hydrator->hydrate($fields, $model);
        return $model;
    }

    protected function getModel(): object
    {
        if (!isset($this->model)) {
            throw new \BadMethodCallException(static::class . " does not have a hydration model specified.");
        }
        $model = $this->model;
        return new $model();
    }

    /**
     * @param array $fields
     * @return object<T>
     */
    public function hydrate(array $fields): object
    {
        return $this->hydrator->hydrate($fields, $this->getModel());
    }

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

        $isCreating = !$model->getExternalId();

        if ($isCreating) {
            $model->setCreatedAt($now);
            $options = [
                'json' => $this->hydrator->extract($model, [
                    ...$this->excluded,
                    ...$this->createFilter,
                ]),
            ];
            $endpoint = $this->router->getEndpoint('post')
                ->setDefaultMethod('post');
        } else {
            $options = [
                'json' => $this->hydrator->extract($model, [
                    ...$this->excluded,
                    ...$this->updateFilter,
                ]),
            ];
            $endpoint = $this->router->getEndpoint('put')
                ->setDefaultMethod('put')
                ->setVariable('id', $model->getExternalId());
        }
        $json = $this->call($endpoint, $options);
        self::$storage = [];

        return $this->hydrate($json);
    }
}
