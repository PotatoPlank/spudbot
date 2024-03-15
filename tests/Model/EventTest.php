<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;


use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Event;
use Spudbot\Model\Guild;
use Spudbot\Types\EventType;

class EventTest extends TestCase
{
    public Event $model;

    public function setUp(): void
    {
        $this->model = new Event();
    }

    /**
     * @test
     * @covers \Spudbot\Model\Event
     */
    public function successfullySetsAndGetsGuild(): void
    {
        $guild = new Guild();

        $this->model->setGuild($guild);

        $this->assertEquals($guild, $this->model->getGuild());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Event
     */
    public function successfullySetsAndGetsChannelId(): void
    {
        $channelId = 'channel id';

        $this->model->setChannelId($channelId);

        $this->assertEquals($channelId, $this->model->getChannelId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Event
     */
    public function successfullySetsAndGetsName(): void
    {
        $name = 'Event';

        $this->model->setName($name);

        $this->assertEquals($name, $this->model->getName());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Event
     */
    public function successfullySetsAndGetsType(): void
    {
        $this->model->setType(EventType::Sesh);
        $this->model->setType(EventType::Native);

        $this->assertEquals(EventType::Native, $this->model->getType());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Event
     */
    public function successfullySetsAndGetsSeshId(): void
    {
        $seshId = 'sesh';

        $this->model->setSeshMessageId($seshId);

        $this->assertEquals($seshId, $this->model->getSeshMessageId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Event
     */
    public function successfullySetsAndGetsNativeId(): void
    {
        $nativeId = 'native';

        $this->model->setNativeEventId($nativeId);

        $this->assertEquals($nativeId, $this->model->getNativeEventId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Event
     */
    public function successfullyGetsAndSetsScheduledDate(): void
    {
        $scheduledAt = Carbon::now();

        $this->model->setScheduledAt($scheduledAt);

        $this->assertEquals($scheduledAt, $this->model->getScheduledAt());
    }
}
