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
use FilesBackup\TestSuite\TestCase;
use Tools\TestSuite\EventAssertTrait;

/**
 * FilesBackupTest class
 */
class FilesBackupTest extends TestCase
{
    use EventAssertTrait;

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
        $expectedFiles = $this->getExpectedFiles(true);
        $target = TMP . 'tmp_' . mt_rand() . '.zip';

        $FilesBackup = new FilesBackup(APP);
        $EventDispatcher = $FilesBackup->getEventDispatcher();
        $this->assertSame($target, $FilesBackup->create($target));
        $this->assertFileExists($target);
        $this->assertEventFiredWithArgs('FilesBackup.zipOpened', [$target], $EventDispatcher);
        foreach ($expectedFiles as $expectedFile) {
            $this->assertEventFiredWithArgs('FilesBackup.fileAdded', [$expectedFile], $EventDispatcher);
        }
        $this->assertEventFiredWithArgs('FilesBackup.zipClosed', [$target], $EventDispatcher);

        $Zipper = new ZipperReader($target);
        array_unshift($expectedFiles, 'TestApp' . DS);
        $this->assertSame($expectedFiles, $Zipper->list());

        $this->expectExceptionMessageMatches('/^File `[\/\w\-\.\:\~\\\_]+` already exists$/');
        $FilesBackup->create($target);
    }

    /**
     * Test for `getAllFiles()` method
     * @test
     */
    public function testGetAllFiles(): void
    {
        $expectedFiles = $this->getExpectedFiles();
        $countExpectedFiles = count($expectedFiles);

        $FilesBackup = new FilesBackup(APP);
        $this->assertSame($expectedFiles, $FilesBackup->getAllFiles());

        $FilesBackup = new FilesBackup(APP, ['git_ignore' => false]);
        $expectedFiles[] = APP . 'vendor' . DS . 'vendor.php';
        $this->assertSame($expectedFiles, $FilesBackup->getAllFiles());

        $FilesBackup = new FilesBackup(APP, ['exclude' => 'subDir/subSubDir']);
        $files = $FilesBackup->getAllFiles();
        $this->assertCount($countExpectedFiles - 1, $files);
        $this->assertNotContains(APP . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile', $files);

        $FilesBackup = new FilesBackup(APP, ['exclude' => ['subDir/subSubDir', 'subDir/anotherSubDir']]);
        $files = $FilesBackup->getAllFiles();
        $this->assertCount($countExpectedFiles - 2, $files);
        $this->assertNotContains(APP . 'subDir' . DS . 'anotherSubDir' . DS . 'anotherSubDirFile', $files);
        $this->assertNotContains(APP . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile', $files);
    }
}
