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
use Spudbot\Events\AbstractEventSubscriber;

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
        $messageReaction->channel->messages->fetch($messageReaction->message_id, true)
            ->done(function (Message $message) {
                $isModerator = $message->member->getPermissions()->moderate_members;
                $isBot = $message->member->user->bot;
                $appliesToGuild = $message->guild_id === self::APPLIED_TO_GUILD_ID;
                $hasReactions = $message->reactions->count();

                if ($isModerator || $isBot || !$appliesToGuild || !$hasReactions) {
                    return;
                }

                $totalReactions = 0;
                foreach ($message->reactions as $reaction) {
                    $totalReactions += $reaction->count;
                }

                $alreadyReacted = isset($this->reactedCache[$message->id]);
                $underThreshold = $totalReactions <= $_ENV['REACTION_ALERT_THRESHOLD'];
                if ($alreadyReacted || $underThreshold) {
                    return;
                }

                $outputChannel = $message->guild->channels->get('id', $_ENV['MOD_ALERT_CHANNEL']);
                if (!$outputChannel) {
                    $this->spud->discord->getLogger()
                        ->error('Unable to access the mod alerts channel.');
                    return;
                }
                $this->reactedCache[$message->id] = 0;

                $builder = $this->spud->interact()
                    ->setTitle('Reaction Count Alert')
                    ->setDescription(
                        $this->spud->twig->render('reaction_alert.twig', [
                            'reactionCount' => $totalReactions,
                            'userId' => $message->member->id,
                            'link' => $message->link,
                            'cacheCount' => count($this->reactedCache),
                            'cacheLimit' => $this->cacheLimit,
                        ])
                    )->sendTo($outputChannel);
            });
    }

    public function canRun(?MessageReaction $messageReaction = null): bool
    {
        return isset($_ENV['MOD_ALERT_CHANNEL']);
    }
}
