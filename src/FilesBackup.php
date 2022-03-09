<?php
declare(strict_types=1);

/**
 * This file is part of cakephp-files-backup.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/cakephp-files-backup
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace FilesBackup;

use ErrorException;
use Symfony\Component\Finder\Finder;
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
     * Construct
     * @param string $source Source directory you want to backup
     */
    public function __construct(string $source)
    {
        $this->source = $source;
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
        $finder->ignoreVCSIgnored(true);

        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files ?? [];
    }
}
