<?php
declare(strict_types=1);

/**
 * This file is part of php-files-backup.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/php-files-backup
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace FilesBackup;

use ErrorException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ZipArchive;

/**
 * FilesBackup class.
 *
 * Allows you to create a backup zip file of a source directory
 */
class FilesBackup
{
    /**
     * Source directory
     * @var string
     */
    protected $source;

    /**
     * Options
     * @var array<string, mixed>
     */
    protected $options;

    /**
     * Constructor.
     *
     * Options:
     *  - `git_ignore`: with `true`, it automatically ignores the files and
     *      directories specified in the `.gitignore` file (default `true`).
     * @param string $source Source directory you want to backup
     * @param array $options Options
     */
    public function __construct(string $source, array $options = [])
    {
        $this->source = $source;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * Option configurations
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver OptionsResolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('git_ignore')
            ->allowedTypes('bool')
            ->default(true);
    }

    /**
     * Creates a zip backup from the source directory
     * @param string $target Zip backup you want to create
     * @return string
     * @throws \ErrorException
     */
    public function create(string $target): string
    {
        $ZipArchive = new ZipArchive();
        if ($ZipArchive->open($target, ZipArchive::CREATE) !== true) {
            throw new ErrorException(sprintf('Unable to create `%s`', $target));
        }

        $appName = basename($this->source);
        $ZipArchive->addEmptyDir($appName);

        foreach ($this->getAllFiles() as $filename) {
            $ZipArchive->addFile($filename, $appName . DS . basename($filename));
        }

        $ZipArchive->close();

        return $target;
    }

    /**
     * Gets all files from the source, excluding files/directories matching the
     *  .gitignore patterns
     * @return array
     */
    public function getAllFiles(): array
    {
        $finder = new Finder();
        $finder->files()->in($this->source);

        if ($this->options['git_ignore']) {
            $finder->ignoreVCSIgnored(true);
        }

        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files ?? [];
    }
}
