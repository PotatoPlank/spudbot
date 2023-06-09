<?php

namespace Spudbot\Bindable\Event\Reactions;


use Carbon\Carbon;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\MessageReaction;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class MessageHasManyReactions extends IBindableEvent
{
    private array $reactedCache = [];
    private int $cacheLimit = 100;

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_REACTION_ADD;
    }

    public function getListener(): callable
    {
        return function (MessageReaction $messageReaction){
            $messageReaction->channel->messages->fetch($messageReaction->message_id, true)->done(function (Message $message){
                $isModerator = $message->member->getPermissions()->moderate_members;
                $isBot = $message->member->user->bot;
                $totalReactions = 0;
                if($message->reactions->count() > 0){
                    foreach($message->reactions as $reaction){
                        $totalReactions += $reaction->count;
                    }
                }
                $messageCached = isset($this->reactedCache[$message->id]);


                if (!$isModerator && !$isBot && !$messageCached && $totalReactions > $_ENV['REACTION_ALERT_THRESHOLD'] && $message->guild_id == '1114365923625816155') {
                    $builder = $this->spud->getSimpleResponseBuilder();
                    $builder->setTitle('Reaction Count Alert');
                    $outputChannel = $message->guild->channels->get('id', $_ENV['MOD_ALERT_CHANNEL']);
                    $this->reactedCache[$message->id] = 0;

                    $context = [
                        'reactionCount' => $totalReactions,
                        'userId' => $message->member->id,
                        'link' => $message->link,
                        'cacheCount' => count($this->reactedCache),
                        'cacheLimit' => $this->cacheLimit,
                    ];
                    $builder->setDescription($this->spud->getTwig()->render('reaction_alert.twig', $context));

                    $outputChannel->sendMessage($builder->getEmbeddedMessage());
                }
            });
        };
    }
}