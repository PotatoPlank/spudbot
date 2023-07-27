<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Meme;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class SprayUser extends IBindableEvent
{
    private array $sprays = [];
    private string $reaction = ':nospray:1115701447569461349';
    private string $refill = 'https://www.contemporist.com/wp-content/uploads/2015/11/bu-water_071115_04.gif';

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            $keywords = [
                'meow',
                'woof',
            ];
            $wordCount = str_word_count($message->content);
            $normalizedMessage = strtolower($message->content);

            if ($wordCount === 1 && in_array($normalizedMessage, $keywords, true)) {
                if (!isset($this->sprays[$message->guild->id])) {
                    $this->sprays[$message->guild->id] = 0;
                }
                $this->sprays[$message->guild->id]++;
                $sprayCount = $this->sprays[$message->guild->id];

                if ($sprayCount % 10 !== 0) {
                    $message->react($this->reaction);
                } else {
                    $response = $this->spud->getSimpleResponseBuilder();
                    $response->setTitle('We\'ll Be Right Back');
                    $response->setOptions(['image' => ['url' => $this->refill]]);
                    $response = $response->getEmbeddedMessage();

                    $message->reply($response)->done(function (Message $responseMessage) use ($message) {
                        $delay = random_int(6, 15);
                        $this->discord->getLoop()->addTimer($delay, function () use ($message, $responseMessage) {
                            $message->react($this->reaction);
                            $responseMessage->delete();
                        });
                    });
                }
            }
        };
    }

    private function stringContains($string, array $array): bool
    {
        $words = explode(' ', $string);
        foreach ($words as $word) {
            foreach ($array as $matchingWord) {
                similar_text($word, $matchingWord, $percent);
                if ($percent > 70) {
                    return true;
                }
            }
        }
        return false;
    }
}