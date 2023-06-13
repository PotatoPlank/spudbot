<?php

namespace Spudbot\Bindable\Command;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Command as CommandPart;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class Setup extends BindableCommand
{
    public function getListener(): callable
    {
        if(empty($this->dbal)){
            throw new \RuntimeException("Command 'setup' requires a DBAL Client to function appropriately.");
        }
        return function (Interaction $interaction){
            $interaction->guild->channels->fetch($interaction->channel_id)->done(function (Channel $channel) use ($interaction){
                if($interaction->member->permissions->manage_guild)
                {
                    $channelId = $channel->id;
                    $threadTypes = [Channel::TYPE_ANNOUNCEMENT_THREAD, Channel::TYPE_PUBLIC_THREAD, Channel::TYPE_PRIVATE_THREAD];
                    $isThread = in_array($channel->type, $threadTypes, true);

                    if($isThread)
                    {
                        $channelId = $channel->parent_id;
                        $threadId = $channel->id;
                    }

                    $repository = new GuildRepository($this->dbal);
                    try{
                        $guild = $repository->findByPart($interaction->guild);

                        $guild->setOutputChannelId($channelId);
                        if($isThread){
                            $guild->setOutputThreadId($threadId);
                        }

                        $repository->save($guild);
                    }catch (\OutOfBoundsException $exception){
                        $guild = new Guild();
                        $guild->setDiscordId($interaction->guild_id);
                        $guild->setOutputChannelId($channelId);
                        if($isThread){
                            $guild->setOutputThreadId($threadId);
                        }

                        $repository->save($guild);
                    }

                    $interaction->respondWithMessage(MessageBuilder::new()->setContent('Established the guild output location.'), true);
                }

                $interaction->respondWithMessage(MessageBuilder::new()->setContent('You don\'t have permission to run that.'));
            });
        };
    }

    public function getCommand(): CommandPart
    {
        $attributes = [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];

        return new Command($this->discord, $attributes);
    }

    public function getName(): string
    {
        return 'setup';
    }

    public function getDescription(): string
    {
        return 'Setup the guild and the selected channel as the log output location.';
    }
}