<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator;

use Carbon\Carbon;
use DateTime;
use Spudbot\Hydrator\Strategy\CarbonStrategy;

class EntityHydrator extends ReflectionHydrator
{
    protected array $fieldMap = [
        'external_id' => 'id',
    ];

    public function __construct()
    {
        $format = DateTime::ATOM;
        $hydrate = function (mixed $value) use ($format) {
            return Carbon::now();
        };
        $extract = function (mixed $value) use ($format) {
            return Carbon::now()->format($format);
        };
        $this->strategies['created_at'] = new CarbonStrategy($format, $hydrate, $extract);
        $this->strategies['updated_at'] = new CarbonStrategy($format, $hydrate, $extract);
    }
}
