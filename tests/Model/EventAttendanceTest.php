<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;


use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Member;

class EventAttendanceTest extends TestCase
{
    public EventAttendance $model;
    public Generator $faker;

    public function setUp(): void
    {
        $this->model = new EventAttendance();
        $this->faker = Factory::create();
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

    /**
     * @test
     * @covers \Spudbot\Model\EventAttendance
     */
    public function successfullyCreates(): void
    {
        $fields = [
            'external_id' => $this->faker->uuid,
            'status' => $this->faker->word,
            'no_show' => $this->faker->boolean,
            'member' => [
                'external_id' => $this->faker->uuid,
            ],
            'event' => [
                'external_id' => $this->faker->uuid,
            ],
            'created_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        ];

        $model = EventAttendance::create($fields);

        $this->assertEquals($fields['external_id'], $model->getExternalId());
        $this->assertEquals($fields['status'], $model->getStatus());
        $this->assertEquals($fields['no_show'], $model->getNoShow());
        $this->assertEquals($fields['member']['external_id'], $model->getMember()->getExternalId());
        $this->assertEquals($fields['event']['external_id'], $model->getEvent()->getExternalId());
        $this->assertEquals($fields['created_at'], $model->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['updated_at'], $model->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
