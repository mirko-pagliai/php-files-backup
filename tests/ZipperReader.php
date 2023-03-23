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
namespace FilesBackup\Test;

use ErrorException;
use Tools\Filesystem;
use ZipArchive;

/**
 * ZipperReader class.
 *
 * Allows you to perform read operations on a zip file. For test use only.
 */
class ZipperReader
{
    /**
     * @var \ZipArchive
     */
    protected ZipArchive $ZipArchive;

    /**
     * Constructor
     * @param string $zipFile Zip archive you want to read
     * @throws \ErrorException
     */
    public function __construct(string $zipFile)
    {
        $this->ZipArchive = new ZipArchive();
        if ($this->ZipArchive->open($zipFile, ZipArchive::RDONLY) !== true) {
            throw new ErrorException(sprintf('Unable to read `%s`', $zipFile));
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->ZipArchive->close();
    }

    /**
     * Counts the files inside a zip archive.
     *
     * Note that this can also take into account empty directories.
     * @return int
     */
    public function count(): int
    {
        return $this->ZipArchive->count();
    }

    /**
     * Lists the files inside a zip archive.
     *
     * Note that this can also take into account empty directories.
     * @return array
     */
    public function list(): array
    {
        $count = $this->count();
        for ($i = 0; $i < $count; $i++) {
            $files[] = Filesystem::instance()->normalizePath($this->ZipArchive->getNameIndex($i) ?: '');
        }

        return $files ?? [];
    }

    /**
     * Extracts a zip archive
     * @param string $target Target directory
     * @return bool
     */
    public function extract(string $target): bool
    {
        return $this->ZipArchive->extractTo($target);
    }
}
