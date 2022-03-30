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
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tools\Event\EventDispatcherTrait;
use Tools\Exceptionist;
use Tools\Filesystem;
use ZipArchive;

/**
 * FilesBackup class.
 *
 * Allows you to create a backup zip file of a source directory
 */
class FilesBackup
{
    use EventDispatcherTrait;

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
     *  - `exclude`: excludes directories from matching with the `exclude()`
     *      method provided by the `Finder` component of Symfony. Directories
     *      passed as argument (string or array of strings) must be relative;
     *  - `git_ignore`: with `true`, it automatically ignores the files and
     *      directories specified in the `.gitignore` file (default `true`).
     * @param string $source Source directory you want to backup
     * @param array<string, mixed> $options Options
     * @throws \Tools\Exception\FileNotExistsException
     * @throws \Tools\Exception\NotReadableException
     */
    public function __construct(string $source, array $options = [])
    {
        Exceptionist::isReadable($source);
        Exceptionist::isDir($source, '`' . $source . '` is not a directory');
        $this->source = $source;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * Options configuration.
     *
     * For more information on options, see the constructor method.
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver OptionsResolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('exclude')
            ->allowedTypes('string', 'string[]');
        $resolver->define('git_ignore')
            ->allowedTypes('bool')
            ->default(true);
    }

    /**
     * Creates a zip backup from the source directory.
     *
     * ### Events
     * This method will trigger some events:
     *  - `FilesBackup.zipOpened`: when the zip file is opened;
     *  - `FilesBackup.zipClosed`: when the zip file is closed;
     *  - `FilesBackup.fileAdded`: when a file is added to the backup.
     * @param string $target Zip backup you want to create
     * @return string
     * @throws \ErrorException
     * @throws \Tools\Exception\FileNotExistsException
     * @throws \Tools\Exception\NotWritableException
     */
    public function create(string $target): string
    {
        Exceptionist::fileNotExists($target, 'File `' . $target . '` already exists');
        Exceptionist::isWritable(dirname($target));

        $ZipArchive = new ZipArchive();
        if ($ZipArchive->open($target, ZipArchive::CREATE) !== true) {
            throw new ErrorException(sprintf('Unable to create `%s`', $target));
        }
        $this->dispatchEvent('FilesBackup.zipOpened', $target);

        //Adds the main directory
        $ZipArchive->addEmptyDir(basename($this->source));
        $this->dispatchEvent('FilesBackup.fileAdded', basename($this->source));

        //Adds all files and directories
        $Filesystem = new Filesystem();
        foreach ($this->getAllFiles() as $filename) {
            $relFilename = $Filesystem->makePathRelative($filename, dirname($this->source));
            $ZipArchive->addFile($filename, $relFilename);
            $this->dispatchEvent('FilesBackup.fileAdded', $relFilename);
        }

        $ZipArchive->close();
        $this->dispatchEvent('FilesBackup.zipClosed', $target);

        return $target;
    }

    /**
     * Gets all files from the source, excluding files/directories matching the
     *  .gitignore patterns
     * @return array<string>
     */
    public function getAllFiles(): array
    {
        $finder = new Finder();
        $finder->files()->in($this->source);

        if (isset($this->options['exclude'])) {
            $finder->exclude($this->options['exclude']);
        }
        if ($this->options['git_ignore']) {
            $finder->ignoreVCSIgnored(true);
        }

        return array_map(function (SplFileInfo $file): string {
            return $file->getRealPath() ?: '';
        }, iterator_to_array($finder->sortByName(), false));
    }
}
