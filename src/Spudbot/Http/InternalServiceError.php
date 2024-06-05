<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Http;

use Throwable;

class InternalServiceError extends \RuntimeException
{
    public function __construct($message, ?Throwable $previous = null, int $statusCode = 500)
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
