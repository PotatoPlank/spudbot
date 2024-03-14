<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;


use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Thread;

class ModelTest extends TestCase
{
    public Thread $model;

    public function setUp(): void
    {
        $this->model = new Thread();
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullySetsAndGetsCreatedAt(): void
    {
        $createdAt = Carbon::now();

        $this->model->setCreatedAt($createdAt);

        $this->assertEquals($createdAt, $this->model->getCreatedAt());
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullySetsAndGetsModifiedAt(): void
    {
        $modifiedAt = Carbon::now();

        $this->model->setModifiedAt($modifiedAt);

        $this->assertEquals($modifiedAt, $this->model->getModifiedAt());
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullySetsAndGetsId(): void
    {
        $id = 1;

        $this->model->setId($id);

        $this->assertEquals($id, $this->model->getId());
    }
}
