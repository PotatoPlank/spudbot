<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Data;

class BotErrorDto
{
    public int $number;
    public string $message;
    public string $file;
    public int $line;
    public array $context;
}
