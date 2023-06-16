<?php

namespace Spudbot\Bindable\Command;

use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class Setup extends IBindableCommand
{
    protected string $name = 'setup';
    protected string $description = 'Setup the guild and the selected channel as the log output location.';
    public function getListener(): callable
    {
        if(empty($this->dbal)){
            throw new \RuntimeException("Command 'setup' requires a DBAL Client to function appropriately.");
        }
        return function (Interaction $interaction){
            $interaction->guild->channels->fetch($interaction->channel_id)->done(function (Channel $channel) use ($interaction){
                $builder = $this->spud->getSimpleResponseBuilder();
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
                    }catch (\OutOfBoundsException $exception){
                        $guild = new Guild();
                        $guild->setDiscordId($interaction->guild_id);
                    }

                    $guild->setOutputChannelId($channelId);
                    if($isThread){
                        $guild->setOutputThreadId($threadId);
                    }
                    //$repository->save($guild);

                    $builder->setTitle('Setup complete');
                    $builder->setDescription("Set the guild output location to <#{$guild->getOutputLocationId()}>.");

                    $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
                }

                $builder->setTitle('Invalid Permissions for Setup');
                $builder->setDescription('You don\'t have the necessary permissions to run this command.');

                $interaction->respondWithMessage($builder->getEmbeddedMessage());
            });
        };
    }
}