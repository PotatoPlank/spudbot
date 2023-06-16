<?php
declare(strict_types=1);

namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Event;
use Spudbot\Repository\SQL\EventRepository;
use Spudbot\Repository\SQL\MemberRepository;

class EventTest extends TestCase
{
    public EventRepository $repository;
    public Connection $dbal;

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
        $this->dbal = DriverManager::getConnection($parameters);
        $this->repository = new EventRepository($this->dbal);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\EventRepository
     * @uses \Spudbot\Helpers\Collection
     * @uses \Spudbot\Interface\IModel
     * @uses \Spudbot\Model\Event
     * @uses \Spudbot\Model\Guild
     * @uses \Spudbot\Repository\SQL\GuildRepository
     */
    public function successfullyGetAllRepositoryRecords(): void
    {
        $collection = $this->repository->getAll();

        $this->assertNotCount(0, $collection);
        $this->assertInstanceOf(Event::class, $collection->get(0));
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\EventRepository
     * @uses \Spudbot\Repository\SQL\GuildRepository
     * @uses \Spudbot\Interface\IModel
     * @uses \Spudbot\Model\Event
     * @uses \Spudbot\Model\Guild
     * @uses \Spudbot\Helpers\Collection
     * @doesNotPerformAssertions
     */
    public function successfullyFindEventById(): void
    {
        $testEvent = 1;

        $this->repository->findById($testEvent);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\EventRepository
     * @uses \Spudbot\Interface\IModel
     * @uses \Spudbot\Model\Event
     */
    public function cannotRetrieveInvalidEventId(): void
    {
        $testEvent = 0;

        $this->expectException(OutOfBoundsException::class);

        $this->repository->findById($testEvent);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\EventRepository
     * @uses \Spudbot\Model\EventAttendance
     * @uses \Spudbot\Helpers\Collection
     * @uses \Spudbot\Repository\SQL\MemberRepository
     * @uses \Spudbot\Model\Member
     * @uses \Spudbot\Interface\IModel
     * @uses \Spudbot\Model\Event
     * @uses \Spudbot\Model\Guild
     * @uses \Spudbot\Repository\SQL\GuildRepository
     * @doesNotPerformAssertions
     */
    public function successfullyRetrievesAttendanceByEvent(): void
    {
        $testEvent = $this->repository->findById(1);

        $this->repository->getAttendanceByEvent($testEvent);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\EventRepository
     * @uses \Spudbot\Model\EventAttendance
     * @uses \Spudbot\Helpers\Collection
     * @uses \Spudbot\Repository\SQL\MemberRepository
     * @uses \Spudbot\Model\Member
     * @uses \Spudbot\Interface\IModel
     * @uses \Spudbot\Model\Event
     * @uses \Spudbot\Model\Guild
     * @uses \Spudbot\Repository\SQL\GuildRepository
     * @doesNotPerformAssertions
     */
    public function successfullyRetrievesAttendanceByEventAndMember(): void
    {
        $memberRepository = new MemberRepository($this->dbal);

        $testEvent = $this->repository->findById(1);
        $testMember = $memberRepository->findById(9);

        $this->repository->getAttendanceByMemberAndEvent($testMember, $testEvent);
    }
}