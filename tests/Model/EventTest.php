<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;


use Carbon\Carbon;
use Faker\Factory;
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
        $this->faker = Factory::create();
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

        $this->model->setDiscordChannelId($channelId);

        $this->assertEquals($channelId, $this->model->getDiscordChannelId());
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

    /**
     * @test
     * @covers \Spudbot\Model\Directory
     */
    public function successfullyCreates(): void
    {
        $fields = [
            'external_id' => $this->faker->uuid,
            'guild' => [
                'external_id' => $this->faker->uuid,
            ],
            'discord_channel_id' => $this->faker->randomNumber(9, true),
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement([EventType::Sesh->value, EventType::Native->value]),
            'sesh_message_id' => $this->faker->randomNumber(9, true),
            'native_event_id' => $this->faker->randomNumber(9, true),
            'scheduled_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'created_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        ];

        $model = Event::create($fields);

        $this->assertEquals($fields['external_id'], $model->getExternalId());
        $this->assertEquals($fields['discord_channel_id'], $model->getDiscordChannelId());
        $this->assertEquals($fields['guild']['external_id'], $model->getGuild()->getExternalId());
        $this->assertEquals($fields['name'], $model->getName());
        $this->assertEquals($fields['type'], $model->getType()->value);
        $this->assertEquals($fields['sesh_message_id'], $model->getSeshMessageId());
        $this->assertEquals($fields['native_event_id'], $model->getNativeEventId());
        $this->assertEquals($fields['scheduled_at'], $model->getScheduledAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['created_at'], $model->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['updated_at'], $model->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
