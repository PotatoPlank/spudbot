<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Reactions;


use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\MessageReaction;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;

class MessageHasManyReactions extends AbstractEventSubscriber
{
    private const APPLIED_TO_GUILD_ID = '1114365923625816155';
    private array $reactedCache = [];
    private int $cacheLimit = 100;

    public function getEventName(): string
    {
        return Event::MESSAGE_REACTION_ADD;
    }

    public function update(?MessageReaction $messageReaction = null): void
    {
        if (!$messageReaction) {
            return;
        }
        $messageReaction->channel->messages->fetch($messageReaction->message_id, true)->done(
            function (Message $message) {
                $isModerator = $message->member->getPermissions()->moderate_members;
                $isBot = $message->member->user->bot;
                if ($isModerator || $isBot || $message->guild_id !== self::APPLIED_TO_GUILD_ID) {
                    return;
                }
                $totalReactions = 0;
                if ($message->reactions->count() > 0) {
                    foreach ($message->reactions as $reaction) {
                        $totalReactions += $reaction->count;
                    }
                }
                if (isset($this->reactedCache[$message->id]) || $totalReactions <= $_ENV['REACTION_ALERT_THRESHOLD']) {
                    return;
                }

                $builder = $this->spud->getSimpleResponseBuilder();
                $builder->setTitle('Reaction Count Alert');
                $outputChannel = $message->guild->channels->get('id', $_ENV['MOD_ALERT_CHANNEL']);
                if (!$outputChannel) {
                    return;
                }
                $this->reactedCache[$message->id] = 0;

                $builder->setDescription($this->spud->twig->render('reaction_alert.twig', [
                    'reactionCount' => $totalReactions,
                    'userId' => $message->member->id,
                    'link' => $message->link,
                    'cacheCount' => count($this->reactedCache),
                    'cacheLimit' => $this->cacheLimit,
                ]));

                $outputChannel->sendMessage($builder->getEmbeddedMessage());
            }
        );
    }
}
