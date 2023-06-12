<?php
declare(strict_types=1);

namespace Repository;


use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Thread;
use Spudbot\Repository\SQL\ThreadRepository;

class ThreadTest extends TestCase
{
    public ThreadRepository $repository;

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
        $this->repository = new ThreadRepository($connection);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\ThreadRepository
     * @uses \Spudbot\Collection
     * @uses \Spudbot\Model
     * @uses \Spudbot\Model\Thread
     * @uses \Spudbot\Repository\SQLRepository
     * @uses \Spudbot\Repository\SQL\GuildRepository
     * @uses \Spudbot\Model\Guild
     */
    public function successfullyGetAllRepositoryRecords(): void
    {
        $collection = $this->repository->getAll();

        $this->assertNotCount(0, $collection);
        $this->assertInstanceOf(Thread::class, $collection->get(0));
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\ThreadRepository
     * @uses \Spudbot\Model
     * @uses \Spudbot\Model\Thread
     * @uses \Spudbot\Collection
     * @uses \Spudbot\Model\Guild
     * @uses \Spudbot\Repository\SQLRepository
     * @uses \Spudbot\Repository\SQL\GuildRepository
     * @doesNotPerformAssertions
     */
    public function successfullyFindThreadById(): void
    {
        $testThread = 59;

        $this->repository->findById($testThread);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\ThreadRepository
     * @uses \Spudbot\Model
     * @uses \Spudbot\Model\Thread
     * @uses \Spudbot\Repository\SQLRepository
     */
    public function cannotRetrieveInvalidThreadId(): void
    {
        $testThread = 0;

        $this->expectException(OutOfBoundsException::class);

        $this->repository->findById($testThread);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\ThreadRepository
     * @doesNotPerformAssertions
     * @todo
     */
//    public function successfullyFindThreadByPart(): void
//    {
//
//    }
}