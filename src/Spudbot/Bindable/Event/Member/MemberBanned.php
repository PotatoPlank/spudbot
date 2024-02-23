<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Member;


use Carbon\Carbon;
use Discord\Parts\Guild\Ban;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class MemberBanned extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_BAN_ADD;
    }

    public function getListener(): callable
    {
        return function (Ban $ban) {
            return;
            $this->discord->getLoop()->addTimer(10, function () use ($ban) {
                $this->discord->guilds->get('id', $ban->guild_id)->bans->fetch($ban->user_id)->done(
                    function (Ban $ban) {
                        $builder = $this->spud->getSimpleResponseBuilder();

                        $modLogChannelId = 1114365924733104133;
                        $publicModLogChannel = $ban->guild->channels->get('id', $modLogChannelId);

                        $context = [
                            'username' => $ban->user->username,
                            'reason' => $ban->reason ?? 'N/A',
                            'timestamp' => Carbon::now()->timestamp,
                        ];
                        $message = $this->spud->twig->render('ban_alert.twig', $context);

                        $builder->setTitle('Member Banned');
                        $builder->setDescription($message);

                        $publicModLogChannel->sendMessage($builder->getEmbeddedMessage());
                    }
                );
            });
        };
    }
}
