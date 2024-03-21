<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spudbot\Util\Recurrence;

final class RecurrenceTest extends TestCase
{

    /**
     * @test
     * @covers \Spudbot\Util\Recurrence
     */
    public function successfullyModifiesRecurrence(): void
    {
        $scheduled = Carbon::parse('2024-03-17 21:00:00');
        $interval = '1 weeks';
        $nextOccurrence = Recurrence::getNextDateTimeFromInterval(
            $scheduled,
            $interval
        );

        $this->assertEquals($scheduled->addDays(7)->toIso8601String(), $nextOccurrence->toIso8601String());
    }
}
