<?php

namespace Model;


use PHPUnit\Framework\TestCase;
use Spudbot\Model\Guild;

class GuildTest extends TestCase
{
    public Guild $model;

    public function setUp(): void
    {
        $this->model = new Guild();
    }

    /**
     * @test
     * @covers \Spudbot\Model\Guild
     */
    public function successfullySetsAndGetsDiscordId(): void
    {
        $discordId = 'discord id';

        $this->model->setDiscordId($discordId);

        $this->assertEquals($discordId, $this->model->getDiscordId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Guild
     */
    public function successfullySetsAndGetsOutputChannelId(): void
    {
        $channelId = 'channel id';

        $this->model->setOutputChannelId($channelId);

        $this->assertEquals($channelId, $this->model->getOutputChannelId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Guild
     */
    public function successfullySetsAndGetsOutputThreadId(): void
    {
        $name = 'thread id';

        $this->model->setOutputThreadId($name);

        $this->assertEquals($name, $this->model->getOutputThreadId());
    }
}