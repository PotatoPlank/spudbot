<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Member;


use Carbon\Carbon;
use Discord\Parts\Guild\Ban;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;

class MemberBanned extends AbstractEventSubscriber
{
    private const MOD_LOG_CHANNEL_ID = 1114365924733104133;

    public function getEventName(): string
    {
        return Event::GUILD_BAN_ADD;
    }

    public function update(?Ban $ban = null): void
    {
        if (!$ban) {
            return;
        }
        $getBan = function () use ($ban) {
            $this->spud->discord->guilds->get('id', $ban->guild_id)->bans->fetch($ban->user_id)
                ->done(function (Ban $ban) {
                    $publicModLogChannel = $ban->guild->channels->get('id', self::MOD_LOG_CHANNEL_ID);
                    if (!$publicModLogChannel) {
                        return;
                    }
                    $username = $ban?->user?->username;
                    if (!$username) {
                        return;
                    }
                    $reason = $ban->reason ?? 'Unknown';
                    $time = Carbon::now()->timestamp;

                    $message = $this->spud->twig->render('ban_alert.twig', [
                        'username' => $username,
                        'reason' => $reason,
                        'timestamp' => $time,
                    ]);


                    $this->spud->interact()
                        ->setTitle('Member Banned')
                        ->setDescription($message)
                        ->sendTo($publicModLogChannel);
                }
                );
        };
        $this->spud->discord->getLoop()->addTimer(10, $getBan);
    }
}
