<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use RuntimeException;
use Spudbot\Interface\AbstractCommandSubscriber;

class FAQ extends AbstractCommandSubscriber
{
    private array $templates = [];

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $this->loadTemplates();
        $this->subCommandObserver->setDefaultListener(function (Interaction $interaction) {
            $builder = $this->spud->getSimpleResponseBuilder();
            $message = 'A related question resource was not found.';
            foreach ($this->templates as $name => $template) {
                if ($interaction->data->options->isset($name)) {
                    $message = $this->spud->twig->render(
                        "faq/{$template}.twig",
                        ['interaction' => $interaction]
                    );
                    break;
                }
            }
            $builder->setTitle('Frequently Asked Questions');
            $builder->setDescription($message);
            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        });

        $this->subCommandObserver->notify($interaction->data->options, $interaction);
    }

    private function loadTemplates(): void
    {
        if (!empty($this->templates)) {
            return;
        }
        $templatePaths = $this->spud->twig->getLoader()->getPaths();
        if (empty($templatePaths) || !isset($templatePaths[0])) {
            throw new RuntimeException('Unable to locate a templating path used with Twig');
        }
        $faqPath = realpath($templatePaths[0]) . '/faq/';

        $files = scandir($faqPath);
        foreach ($files as $file) {
            $path = $faqPath . $file;
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($file && $file !== '..' && $file !== '.' && is_file($path)) {
                $this->templates[$name] = $name;
            }
        }
    }

    public function getCommand(): Command
    {
        $this->loadTemplates();
        $command = CommandBuilder::new();
        $command->setName($this->getCommandName());
        $command->setDescription($this->getCommandDescription());
        foreach ($this->templates as $name => $path) {
            $subCommand = new Option($this->spud->discord);
            $subCommand->setName($name);
            $subCommand->setDescription($this->spud->twig->render("faq_description/{$name}.twig"));
            $subCommand->setType(Option::SUB_COMMAND);
            $command->addOption($subCommand);
        }

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'faq';
    }

    public function getCommandDescription(): string
    {
        return 'A list of frequently asked questions.';
    }
}
