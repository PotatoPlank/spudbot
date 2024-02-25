<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Carbon\Carbon;

require_once "vendor/autoload.php";

$build = [
    'date' => Carbon::now('America/New_York')->toDateTimeString(),
];
file_put_contents('build.json', json_encode($build, JSON_THROW_ON_ERROR));
