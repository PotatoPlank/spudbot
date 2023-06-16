<?php
declare(strict_types=1);

namespace Repository;


use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Member;
use Spudbot\Repository\SQL\MemberRepository;

class MemberTest extends TestCase
{
    public MemberRepository $repository;

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
        $this->repository = new MemberRepository($connection);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\MemberRepository
     * @uses \Spudbot\Collection
     * @uses \Spudbot\Model
     * @uses \Spudbot\Model\Member
     * @uses \Spudbot\Model\Guild
     * @uses \Spudbot\Repository\SQL\GuildRepository
     */
    public function successfullyGetAllRepositoryRecords(): void
    {
        $collection = $this->repository->getAll();

        $this->assertNotCount(0, $collection);
        $this->assertInstanceOf(Member::class, $collection->get(0));
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\MemberRepository
     * @uses \Spudbot\Model
     * @uses \Spudbot\Model\Member
     * @uses \Spudbot\Collection
     * @uses \Spudbot\Model\Guild
     * @uses \Spudbot\Repository\SQL\GuildRepository
     * @doesNotPerformAssertions
     */
    public function successfullyFindMemberById(): void
    {
        $testMember = 8;

        $this->repository->findById($testMember);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\MemberRepository
     * @uses \Spudbot\Repository\SQL\GuildRepository
     * @uses \Spudbot\Model
     * @uses \Spudbot\Model\Member
     * @uses \Spudbot\Model\Guild
     */
    public function cannotRetrieveInvalidMemberId(): void
    {
        $testMember = 0;

        $this->expectException(OutOfBoundsException::class);

        $this->repository->findById($testMember);
    }

    /**
     * @test
     * @covers \Spudbot\Repository\SQL\MemberRepository
     * @doesNotPerformAssertions
     * @todo
     */
//    public function successfullyFindThreadByPart(): void
//    {
//
//    }
}