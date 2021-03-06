<?php

namespace marvin255\bxcodegen\generator;

use marvin255\bxcodegen\service\options\CollectionInterface;
use marvin255\bxcodegen\service\filesystem\Directory;
use marvin255\bxcodegen\ServiceLocatorInterface;
use InvalidArgumentException;

/**
 * Генератор для создания конфигурационных файлов Rocketeer.
 */
class Rocketeer extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function generate(CollectionInterface $options, ServiceLocatorInterface $locator)
    {
        $rocketeerFolder = $options->get('rocketeer_folder', '.rocketeer');
        $templateData = $this->collectDataFromInputForTemplate($options);
        $sourcePath = $options->get('source', dirname(dirname(__DIR__)) . '/templates/rocketeer');
        $destinationPath = $locator->get('pathManager')->getAbsolutePath("/{$rocketeerFolder}");

        $copier = $this->getAndConfigurateCopierFromLocator($locator, $templateData);
        $source = new Directory($sourcePath);
        $destination = new Directory($destinationPath);

        if ($destination->isExists()) {
            throw new InvalidArgumentException(
                'Directory ' . $destination->getPathname() . ' already exists'
            );
        }

        $copier->copyDir($source, $destination);

        if ($options->get('gitignore_inject', false)) {
            $gitignorePath = $locator->get('pathManager')->getAbsolutePath(
                $options->get('gitignore_path', '.gitignore')
            );
            $gitignoreData = '.rocketeer/logs';
            if (!file_exists($gitignorePath)) {
                file_put_contents($gitignorePath, $gitignoreData);
            } elseif (mb_strpos(file_get_contents($gitignorePath), $gitignoreData) === false) {
                file_put_contents($gitignorePath, "\r\n\r\n" . $gitignoreData, FILE_APPEND);
            }
        }

        if ($options->get('phar_inject', false)) {
            $pharUrl = $options->get('phar_url', 'http://rocketeer.autopergamene.eu/versions/rocketeer.phar');
            $pharPath = $locator->get('pathManager')->getAbsolutePath(
                'rocketeer.phar'
            );
            $fh = @fopen($pharUrl, 'r');
            if ($fh === false) {
                throw new InvalidArgumentException(
                    "Can't open {$pharUrl} for download"
                );
            }
            file_put_contents($pharPath, $fh);
            fclose($fh);
        }
    }

    /**
     * Собирает массив опций для шаблонов из тех опций, что пришли от пользователя.
     *
     * @param \marvin255\bxcodegen\service\options\CollectionInterface $options
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function collectDataFromInputForTemplate(CollectionInterface $options)
    {
        return [
            'application_name' => $options->get('application_name', ''),
            'root_directory' => $options->get('root_directory', ''),
            'repository' => $options->get('repository', ''),
            'branch' => $options->get('branch', 'master'),
            'host' => $options->get('host', ''),
            'username' => $options->get('username', ''),
            'password' => $options->get('password', ''),
            'key' => $options->get('key', ''),
            'keyphrase' => $options->get('keyphrase', ''),
        ];
    }
}
