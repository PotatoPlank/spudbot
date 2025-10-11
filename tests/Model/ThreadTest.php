<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;


use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;

class ThreadTest extends TestCase
{
    public Thread $model;

    public function setUp(): void
    {
        $this->model = new Thread();
        $this->faker = Factory::create();
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
     * @uses   Guild
     */
    public function successfullySetsAndGetsGuild(): void
    {
        $guild = new Guild();

        $this->model->setGuild($guild);

        $this->assertEquals($guild, $this->model->getGuild());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Thread
     */
    public function successfullyCreates(): void
    {
        $fields = [
            'external_id' => $this->faker->uuid,
            'discord_id' => $this->faker->randomNumber(9, true),
            'guild' => [
                'external_id' => $this->faker->uuid,
            ],
            'channel' => [
                'external_id' => $this->faker->uuid,
            ],
            'tag' => $this->faker->word,
            'created_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        ];

        $model = Thread::create($fields);

        $this->assertEquals($fields['external_id'], $model->getExternalId());
        $this->assertEquals($fields['discord_id'], $model->getDiscordId());
        $this->assertEquals($fields['guild']['external_id'], $model->getGuild()->getExternalId());
        $this->assertEquals($fields['channel']['external_id'], $model->getChannel()->getExternalId());
        $this->assertEquals($fields['tag'], $model->getTag());
        $this->assertEquals($fields['created_at'], $model->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['updated_at'], $model->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
