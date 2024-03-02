<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Repository;


use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class GuildTest extends TestCase
{
    public GuildRepository $repository;

    public function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        $parameters = [
            'dbname' => $_ENV['DATABASE_NAME'],
            'user' => $_ENV['DATABASE_USERNAME'],
            'password' => $_ENV['DATABASE_PASSWORD'],
            'host' => $_ENV['DATABASE_HOST'],
            'driver' => $_ENV['DATABASE_DRIVER'],
        ];
        $connection = DriverManager::getConnection($parameters);
        $this->repository = new GuildRepository($connection);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\GuildRepository
     * @uses   \Spudbot\Helpers\Collection
     * @uses   \Spudbot\Interface\AbstractModel
     * @uses   \Spudbot\Model\Guild
     */
    public function successfullyGetAllRepositoryRecords(): void
    {
        $collection = $this->repository->getAll();

        $this->assertNotCount(0, $collection);
        $this->assertInstanceOf(Guild::class, $collection->get(0));
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\GuildRepository
     * @uses   \Spudbot\Interface\AbstractModel
     * @uses   \Spudbot\Model\Guild
     * @uses   \Spudbot\Helpers\Collection
     * @doesNotPerformAssertions
     */
    public function successfullyFindGuildById(): void
    {
        $testGuild = 3;

        $this->repository->findById($testGuild);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\GuildRepository
     * @uses   \Spudbot\Interface\AbstractModel
     * @uses   \Spudbot\Model\Guild
     * @uses   \Spudbot\Helpers\Collection
     */
    public function cannotRetrieveInvalidGuildId(): void
    {
        $testGuild = 0;

        $this->expectException(OutOfBoundsException::class);

        $this->repository->findById($testGuild);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\GuildRepository
     * @doesNotPerformAssertions
     * @todo
     */
//    public function successfullyFindGuildByPart(): void
//    {
//
//    }
}