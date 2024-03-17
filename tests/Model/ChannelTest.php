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
use Spudbot\Model\Channel;

class ChannelTest extends TestCase
{
    public Generator $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create();
    }

    /**
     * @test
     * @covers \Spudbot\Model\Channel
     */
    public function successfullyCreates(): void
    {
        $fields = [
            'external_id' => $this->faker->uuid,
            'discord_id' => $this->faker->randomNumber(9, true),
            'guild' => [
                'external_id' => $this->faker->uuid,
            ],
            'created_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        ];

        $model = Channel::create($fields);

        $this->assertEquals($fields['external_id'], $model->getExternalId());
        $this->assertEquals($fields['discord_id'], $model->getDiscordId());
        $this->assertEquals($fields['guild']['external_id'], $model->getGuild()->getExternalId());
        $this->assertEquals($fields['created_at'], $model->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['updated_at'], $model->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
