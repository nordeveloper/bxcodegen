<?php

namespace marvin255\bxcodegen\cli;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use marvin255\bxcodegen\service\options\Collection;

/**
 * Консольная команда для Symfony console, которая запускает
 * генератор компонента.
 */
class ComponentCommand extends AbstractCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('bxcodegen:component')
            ->setDescription('Create component for bitrix')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of component namespace:name'
            )
            ->addOption(
                'title',
                't',
                InputOption::VALUE_REQUIRED,
                'Readable title for component'
            );
    }

    /**
     * @inheritdoc
     */
    protected function collectGeneratorNameFromInput(InputInterface $input)
    {
        return 'component';
    }

    /**
     * @inheritdoc
     */
    protected function collectOptionsFromInput(InputInterface $input)
    {
        $return = [
            'name' => $input->getArgument('name'),
        ];

        if ($input->getOption('title') !== null) {
            $return['title'] = $input->getOption('title');
        }

        return new Collection($return);
    }
}
