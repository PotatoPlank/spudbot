<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

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

        $this->model->setChannelAnnounceId($channelId);

        $this->assertEquals($channelId, $this->model->getChannelAnnounceId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Guild
     */
    public function successfullySetsAndGetsOutputThreadId(): void
    {
        $name = 'thread id';

        $this->model->setChannelThreadAnnounceId($name);

        $this->assertEquals($name, $this->model->getChannelThreadAnnounceId());
    }
}
