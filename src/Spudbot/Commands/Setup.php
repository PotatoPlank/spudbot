<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Permissions\Permission;
use Spudbot\SubCommands\Setup\SetIntroChannel;
use Spudbot\SubCommands\Setup\SetLogChannel;
use Spudbot\SubCommands\Setup\SetMarketplaceChannel;
use Spudbot\SubCommands\Setup\SetModAlertChannel;
use Spudbot\SubCommands\Setup\SetPublicModLog;
use Spudbot\SubCommands\Setup\SetTenuredRole;
use Spudbot\SubCommands\Setup\SetVerifiedChannel;
use Spudbot\SubCommands\Setup\SetVerifiedRole;

class Setup extends AbstractCommandSubscriber
{

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        if (!$interaction->member->permissions->manage_guild) {
            $this->spud->interact()
                ->error('You don\'t have the necessary permissions to run this command.')
                ->respondTo($interaction);
            return;
        }
        $this->subCommandObserver->subscribeAll([
            SetLogChannel::class,
            SetPublicModLog::class,
            SetModAlertChannel::class,
            SetIntroChannel::class,
            SetMarketplaceChannel::class,
            SetVerifiedChannel::class,
            SetVerifiedRole::class,
            SetTenuredRole::class,
        ]);
        $this->subCommandObserver->notify($interaction->data->options, $interaction);
    }

    public function getCommand(): Command
    {
        $options = [
            'channel_id' => $this->spud->commandOption('channel_id', 'The channel to target.')
                ->setRequired()->asChannel()->create(),
        ];
        $role = $this->spud->commandOption('role_id', 'The role to target.')
            ->setRequired()->asRole()->create();
        $subCommands = [
            [
                'name' => 'log_channel',
                'description' => 'Establish a bot log channel.',
                'options' => $options,
            ],
            [
                'name' => 'public_log_channel',
                'description' => 'Establish a log channel for public actions.',
                'options' => $options,
            ],
            [
                'name' => 'mod_alert_channel',
                'description' => 'Establish a mod alert channel for reviewable actions.',
                'options' => $options,
            ],
            [
                'name' => 'intro_channel',
                'description' => 'Establish a user introduction channel.',
                'options' => $options,
            ],
            [
                'name' => 'marketplace_channel',
                'description' => 'Establish a marketplace channel.',
                'options' => $options,
            ],
            [
                'name' => 'verified_channel',
                'description' => 'Establish a verified channel.',
                'options' => $options,
            ],
            [
                'name' => 'verified_role',
                'description' => 'Establish a verified role.',
                'options' => [
                    'role_id' => $role,
                ],
            ],
            [
                'name' => 'tenured_role',
                'description' => 'Establish a member tenure role.',
                'options' => [
                    'role_id' => $role,
                ],
            ],
        ];

        $command = $this->spud->command($this->getCommandName(), $this->getCommandDescription());

        foreach ($subCommands as $info) {
            $subCommand = $this->spud
                ->commandOption($info['name'], $info['description'])
                ->setType(Option::SUB_COMMAND)
                ->setOptions($info['options'])->create();

            $command->addOption(
                $info['name'],
                $subCommand
            );
        }

        $command->setDefaultPermissions(Permission::ROLE_PERMISSIONS['manage_guild']);

        return $command->create();
    }

    public function getCommandName(): string
    {
        return 'setup';
    }

    public function getCommandDescription(): string
    {
        return 'Setup the guild and the selected channel as the log output location.';
    }
}
