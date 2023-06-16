<?php

namespace Spudbot\Bindable\Command;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use RuntimeException;
use Spudbot\Interface\IBindableCommand;

class FAQ extends IBindableCommand
{
    protected string $name = 'faq';
    protected string $description = 'A list of frequently asked questions.';
    private array $templates = [];

    private function loadTemplates(): void
    {
        if(empty($this->templates)){
            $templatePaths = $this->spud->getTwig()->getLoader()->getPaths();
            if(empty($templatePaths) || !isset($templatePaths[0])){
                Throw New RuntimeException('Unable to locate a templating path used with Twig');
            }
            $faqPath = realpath($templatePaths[0]) . '/faq/';

            $files = scandir($faqPath);
            foreach($files as $file){
                $path = $faqPath . $file;
                $name = pathinfo($file, PATHINFO_FILENAME);
                if($file && $file !== '..' && $file !== '.' && is_file($path)){
                    $this->templates[$name] = $name;
                }
            }
        }
    }

    public function getListener(): callable
    {
        $this->loadTemplates();
        return function (Interaction $interaction){
            $this->observer->setDefaultListener(function (Interaction $interaction){
                $builder = $this->spud->getSimpleResponseBuilder();
                $message = 'A related question resource was not found.';
                foreach($this->templates as $name => $template){
                    if($interaction->data->options->isset($name)){
                        $message = $this->spud->getTwig()->render("faq/{$template}.twig", ['interaction' => $interaction]);
                        break;
                    }
                }
                $builder->setTitle('Frequently Asked Questions');
                $builder->setDescription($message);
                $interaction->respondWithMessage($builder->getEmbeddedMessage());
            });

            $this->observer->notify($interaction->data->options, $interaction);
        };
    }

    public function getCommand(): Command
    {
        $this->loadTemplates();
        $command = CommandBuilder::new();
        $command->setName($this->getName());
        $command->setDescription($this->getDescription());
        foreach($this->templates as $name => $path){
            $subCommand = new Option($this->discord);
            $subCommand->setName($name);
            $subCommand->setDescription($this->spud->getTwig()->render("faq_description/{$name}.twig"));
            $subCommand->setType(Option::SUB_COMMAND);
            $command->addOption($subCommand);
        }

        return new Command($this->discord, $command->toArray());
    }
}