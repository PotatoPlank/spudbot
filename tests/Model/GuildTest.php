<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;


use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Guild;

class GuildTest extends TestCase
{
    public Guild $model;

    public function setUp(): void
    {
        $this->model = new Guild();
        $this->faker = Factory::create();
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

    /**
     * @test
     * @covers \Spudbot\Model\Guild
     */
    public function successfullyCreates(): void
    {
        $fields = [
            'external_id' => $this->faker->uuid,
            'discord_id' => $this->faker->randomNumber(9, true),
            'channel_announce_id' => $this->faker->randomNumber(9, true),
            'channel_thread_announce_id' => $this->faker->randomNumber(9, true),
            'created_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        ];

        $model = Guild::create($fields);

        $this->assertEquals($fields['external_id'], $model->getExternalId());
        $this->assertEquals($fields['discord_id'], $model->getDiscordId());
        $this->assertEquals($fields['channel_announce_id'], $model->getChannelAnnounceId());
        $this->assertEquals($fields['channel_thread_announce_id'], $model->getChannelThreadAnnounceId());
        $this->assertEquals($fields['created_at'], $model->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['updated_at'], $model->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
