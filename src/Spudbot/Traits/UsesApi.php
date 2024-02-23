<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

trait UsesApi
{
    public function __construct(protected Client $client)
    {
    }

    public function getResponseJson(ResponseInterface $response)
    {
        return json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
    }
}