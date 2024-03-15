<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;


use PHPUnit\Framework\TestCase;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Member;

class EventAttendanceTest extends TestCase
{
    public EventAttendance $model;

    public function setUp(): void
    {
        $this->model = new EventAttendance();
    }

    /**
     * @test
     * @covers \Spudbot\Model\EventAttendance
     */
    public function successfullySetsAndGetsEvent(): void
    {
        $event = new Event();

        $this->model->setEvent($event);

        $this->assertEquals($event, $this->model->getEvent());
    }

    /**
     * @test
     * @covers \Spudbot\Model\EventAttendance
     */
    public function successfullySetsAndGetsMember(): void
    {
        $member = new Member();

        $this->model->setMember($member);

        $this->assertEquals($member, $this->model->getMember());
    }

    /**
     * @test
     * @covers \Spudbot\Model\EventAttendance
     */
    public function successfullyGetsAndSetsStatus(): void
    {
        $status = 'Status';

        $this->model->setStatus($status);

        $this->assertEquals($status, $this->model->getStatus());
    }

    /**
     * @test
     * @covers \Spudbot\Model\EventAttendance
     */
    public function successfullySetsAndGetsNoShowStatus(): void
    {
        $this->model->setNoShow(true);

        $this->assertTrue($this->model->getNoShow());
    }
}
