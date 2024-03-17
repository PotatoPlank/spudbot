<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Model;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spudbot\Model\Member;

class AbstractModelTest extends TestCase
{
    public Member $model;

    public function setUp(): void
    {
        $this->model = new Member();
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullyMutatesCarbonDateField(): void
    {
        $now = Carbon::now();

        $this->model->mutate('created_at', $now);
        $this->model->mutate('updated_at', $now);

        $this->assertTrue($this->model->getCreatedAt()->equalTo($now));
        $this->assertTrue($this->model->getUpdatedAt()->equalTo($now));
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullyMutatesStringDateField(): void
    {
        $now = Carbon::now();

        $this->model->mutate('created_at', $now->toIso8601String());
        $this->model->mutate('updated_at', $now->toIso8601String());

        $this->assertEquals($now->toIso8601String(), $this->model->getCreatedAt()->toIso8601String());
        $this->assertEquals($now->toIso8601String(), $this->model->getUpdatedAt()->toIso8601String());
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullyMutatesStringIntField(): void
    {
        $string = '10';

        $this->model->mutate('total_comments', $string);

        $this->assertEquals((int)$string, $this->model->getTotalComments());
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullyMutatesIntIntField(): void
    {
        $int = 10;

        $this->model->mutate('total_comments', $int);

        $this->assertEquals($int, $this->model->getTotalComments());
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullyMutatesIntStringField(): void
    {
        $int = 10;

        $this->model->mutate('username', $int);

        $this->assertEquals((string)$int, $this->model->getUsername());
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function successfullyMutatesStringStringField(): void
    {
        $string = 'Test';

        $this->model->mutate('username', $string);

        $this->assertEquals($string, $this->model->getUsername());
    }

    /**
     * @test
     * @covers \Spudbot\Model\AbstractModel
     */
    public function setsPropertyWithoutMethod(): void
    {
        $this->model->mutate('invalidMethod', 'Value');
        $this->assertEquals('Value', $this->model->invalidMethod);
    }
}
