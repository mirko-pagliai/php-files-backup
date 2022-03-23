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
namespace FilesBackup\TestSuite;

use Tools\TestSuite\TestCase as BaseTestCase;

/**
 * TestCase class.
 */
class TestCase extends BaseTestCase
{
    /**
     * Internal method to get the expected files from a standard (default options)
     *  backup of `APP` directory
     * @param bool $relativePath If `true`, paths will be relative
     * @return array
     */
    protected function getExpectedFiles(bool $relativePath = false): array
    {
        $dir = $relativePath ? array_value_last(array_filter(explode(DS, APP))) . DS : APP;

        return [
            $dir . 'example.php',
            $dir . 'empty',
            $dir . '400x400.jpeg',
            $dir . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile',
            $dir . 'subDir' . DS . 'subDirFile',
        ];
    }
}
