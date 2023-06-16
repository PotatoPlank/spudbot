<?php

namespace Spudbot\Bindable\Command;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Command as CommandPart;
use Discord\Parts\Interactions\Command\Permission;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Permissions\ChannelPermission;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQL\GuildRepository;

class MemberCount extends BindableCommand
{
    public function getListener(): callable
    {
        return function (Interaction $interaction){
            $builder = $this->spud->getSimpleResponseBuilder();
            if($interaction->member->permissions->manage_guild){
                $memberCount = $interaction->guild->member_count;
                $categoryName = 'Member Count 📈';
                /**
                 * Todo support in setup
                 */
                $category = $interaction->guild->channels->get('name', $categoryName);
                if(!$category)
                {
                    $category = new Channel($this->discord);
                    $category->type = Channel::TYPE_CATEGORY;
                    $category->name = $categoryName;
                    $interaction->guild->channels->save($category);
                }
                $channel = $interaction->guild->channels->get('parent_id', $category->id);
                if(!$channel)
                {
                    $everyoneRole = $interaction->guild->roles->get('name', '@everyone');
                    $channel = new Channel($this->discord);
                    $channel->type = Channel::TYPE_VOICE;
                    $channel->name = "Member Count: {$memberCount}";
                    $channel->setPermissions($everyoneRole, [
                        'view_channel',
                    ], [
                        'connect',
                    ]);
                    $channel->parent_id = $category->id;
                }else{
                    $channel->name = "Member Count: {$memberCount}";
                }

                $interaction->guild->channels->save($channel);


                $builder->setTitle('Member Counter');
                $builder->setDescription("The member counter was updated to: {$memberCount}");

                $interaction->respondWithMessage($builder->getEmbeddedMessage());
            }else{
                $builder->setTitle('Invalid Permissions for Counter');
                $builder->setDescription('You don\'t have the necessary permissions to run this command.');

                $interaction->respondWithMessage($builder->getEmbeddedMessage());
            }
        };
    }

    public function getCommand(): Command
    {
        $attributes = [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];

        return new Command($this->discord, $attributes);
    }

    public function getName(): string
    {
        return 'counter';
    }

    public function getDescription(): string
    {
        return 'Update the member counter.';
    }
}