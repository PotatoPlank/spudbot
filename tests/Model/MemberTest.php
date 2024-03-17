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
use Spudbot\Model\Member;

class MemberTest extends TestCase
{
    public Member $model;

    public function setUp(): void
    {
        $this->model = new Member();
        $this->faker = Factory::create();
    }

    /**
     * @test
     * @covers \Spudbot\Model\Member
     */
    public function successfullySetsAndGetsDiscordId(): void
    {
        $discordId = 'discord id';

        $this->model->setDiscordId($discordId);

        $this->assertEquals($discordId, $this->model->getDiscordId());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Member
     * @uses   \Spudbot\Model\Guild
     */
    public function successfullySetsAndGetsGuild(): void
    {
        $guild = new Guild();

        $this->model->setGuild($guild);

        $this->assertEquals($guild, $this->model->getGuild());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Member
     */
    public function successfullySetsAndGetsTotalComments(): void
    {
        $totalComments = 1;

        $this->model->setTotalComments($totalComments);

        $this->assertEquals($totalComments, $this->model->getTotalComments());
    }

    /**
     * @test
     * @covers \Spudbot\Model\Member
     */
    public function successfullyCreates(): void
    {
        $fields = [
            'external_id' => $this->faker->uuid,
            'discord_id' => $this->faker->randomNumber(9, true),
            'total_comments' => $this->faker->randomNumber(),
            'username' => $this->faker->userName,
            'guild' => [
                'external_id' => $this->faker->uuid,
            ],
            'verified_by' => null,
            'created_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime->format('Y-m-d H:i:s'),
        ];

        $model = Member::create($fields);

        $this->assertEquals($fields['external_id'], $model->getExternalId());
        $this->assertEquals($fields['discord_id'], $model->getDiscordId());
        $this->assertEquals($fields['total_comments'], $model->getTotalComments());
        $this->assertEquals($fields['username'], $model->getUsername());
        $this->assertEquals($fields['guild']['external_id'], $model->getGuild()->getExternalId());
        $this->assertEquals($fields['verified_by'], $model->getVerifiedBy());
        $this->assertEquals($fields['created_at'], $model->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($fields['updated_at'], $model->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
