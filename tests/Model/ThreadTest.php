<?php

namespace Model;


use PHPUnit\Framework\TestCase;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;

class ThreadTest extends TestCase
{
    public Thread $model;

    public function setUp(): void
    {
        $this->model = new Thread();
    }

    /**
     * @test
     * @covers \Spudbot\Model\Thread
     */
    public function successfullySetsAndGetsDiscordId(): void
    {
        $discordId = 'discord id';

        $this->model->setDiscordId($discordId);

        $this->assertEquals($discordId, $this->model->getDiscordId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Thread
     * @uses \Spudbot\Model\Guild
     */
    public function successfullySetsAndGetsGuild(): void
    {
        $guild = new Guild();

        $this->model->setGuild($guild);

        $this->assertEquals($guild, $this->model->getGuild());
    }
}