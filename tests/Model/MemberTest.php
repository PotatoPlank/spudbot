<?php

namespace Model;


use PHPUnit\Framework\TestCase;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;

class MemberTest extends TestCase
{
    public Member $model;

    public function setUp(): void
    {
        $this->model = new Member();
    }

    /**
     * @test
     * @covers \Spudbot\Model\Member
     */
    public function successfullySetsAndGetsDiscordId(): void
    {
        $discordId = 'discord id';

        $this->model->setDiscordId($discordId);

        $this->assertEquals($discordId, $this->model->getDiscordId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Member
     * @uses \Spudbot\Model\Guild
     */
    public function successfullySetsAndGetsGuild(): void
    {
        $guild = new Guild();

        $this->model->setGuild($guild);

        $this->assertEquals($guild, $this->model->getGuild());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Member
     */
    public function successfullySetsAndGetsTotalComments(): void
    {
        $totalComments = 1;

        $this->model->setTotalComments($totalComments);

        $this->assertEquals($totalComments, $this->model->getTotalComments());
    }
}