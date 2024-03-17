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
use Spudbot\Model\Directory;

class DirectoryTest extends TestCase
{
    public Generator $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create();
    }

    /**
     * @test
     * @covers \Spudbot\Model\Directory
     */
    public function successfullyCreates(): void
    {
        $fields = [
            'external_id' => $this->faker->uuid,
            'embed_id' => $this->faker->randomNumber(9, true),
            'directory_channel' => [
                'external_id' => $this->faker->uuid,
            ],
            'forum_channel' => [
                'external_id' => $this->faker->uuid,
            ],
            'created_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        ];

        $model = Directory::create($fields);

        $this->assertEquals($fields['external_id'], $model->getExternalId());
        $this->assertEquals($fields['embed_id'], $model->getEmbedId());
        $this->assertEquals(
            $fields['directory_channel']['external_id'],
            $model->getDirectoryChannel()->getExternalId()
        );
        $this->assertEquals($fields['forum_channel']['external_id'], $model->getForumChannel()->getExternalId());
        $this->assertEquals($fields['created_at'], $model->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['updated_at'], $model->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
