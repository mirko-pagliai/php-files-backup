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
namespace FilesBackup\Test\TestCase;

use FilesBackup\FilesBackup;
use FilesBackup\Test\ZipperReader;
use Tools\TestSuite\TestCase;

/**
 * FilesBackupTest class
 */
class FilesBackupTest extends TestCase
{
    /**
     * Test for `__construct()` method, with a no directory source
     * @test
     */
    public function testConstructorNoDirectorySource(): void
    {
        $file = tempnam(TMP, 'tmp') ?: '';
        $this->expectExceptionMessage('`' . $file . '` is not a directory');
        new FilesBackup($file);
    }

    /**
     * Test for `create()` method
     * @test
     */
    public function testCreate(): void
    {
        $FilesBackup = new FilesBackup(APP);
        $target = $FilesBackup->create(TMP . 'tmp_' . mt_rand() . '.zip');
        $this->assertFileExists($target);

        $Zipper = new ZipperReader($target);
        $files = $Zipper->list();
        $this->assertContains('TestApp' . DS . 'example.php', $files);
        $this->assertContains('TestApp' . DS . 'empty', $files);
        $this->assertContains('TestApp' . DS . '400x400.jpeg', $files);

        $this->expectExceptionMessageMatches('/^File `[\/\w\-\.\:\~\\\_]+` already exists$/');
        $FilesBackup->create($target);
    }

    /**
     * Test for `getAllFiles()` method
     * @test
     */
    public function testGetAllFiles(): void
    {
        $FilesBackup = new FilesBackup(APP);
        $files = $FilesBackup->getAllFiles();
        $this->assertContains(APP . 'example.php', $files);
        $this->assertContains(APP . 'empty', $files);
        $this->assertContains(APP . '400x400.jpeg', $files);
        $this->assertNotContains(APP . 'vendor' . DS . 'vendor.php', $files);

        $FilesBackup = new FilesBackup(APP, ['git_ignore' => false]);
        $files = $FilesBackup->getAllFiles();
        $this->assertContains(APP . 'vendor' . DS . 'vendor.php', $files);
    }
}
