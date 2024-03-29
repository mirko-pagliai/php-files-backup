<?php
/** @noinspection PhpUnhandledExceptionInspection */
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
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Tools\TestSuite\EventAssertTrait;
use ZipArchive;

/**
 * FilesBackupTest class
 */
class FilesBackupTest extends TestCase
{
    use EventAssertTrait;

    /**
     * Test for `__construct()` method, with a no directory source
     * @test
     * @uses \FilesBackup\FilesBackup::__construct()
     */
    public function testConstructorNoDirectorySource(): void
    {
        $file = tempnam(TMP, 'tmp') ?: '';
        $this->expectExceptionMessage('`' . $file . '` is not a directory');
        new FilesBackup($file);
    }

    /**
     * Test for `__construct()` method, with a bad option
     * @test
     * @uses \FilesBackup\FilesBackup::__construct()
     */
    public function testConstructorBadOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new FilesBackup(APP, ['git_ignore' => 'string']);
    }

    /**
     * @test
     * @uses \FilesBackup\FilesBackup::create()
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

        /**
         * With a `ZipArchive` failure
         */
        $FilesBackup = $this->getMockBuilder(FilesBackup::class)
            ->onlyMethods(['getZipArchive'])
            ->setConstructorArgs([APP])
            ->getMock();
        $FilesBackup->method('getZipArchive')->willReturnCallback(function (): ZipArchive {
            $ZipArchive = $this->createPartialMock(ZipArchive::class, ['open']);
            $ZipArchive->method('open')->willReturn(false);

            return $ZipArchive;
        });
        $target = TMP . 'tmp_' . mt_rand() . '.zip';
        $this->expectExceptionMessage('Unable to create `' . $target . '`');
        $FilesBackup->create($target);
    }

    /**
     * @test
     * @uses \FilesBackup\FilesBackup::getAllFiles()
     */
    public function testGetAllFiles(): void
    {
        $expectedFiles = $this->getExpectedFiles();
        $countExpectedFiles = count($expectedFiles);

        $FilesBackup = new FilesBackup(APP);
        $this->assertSame($expectedFiles, $FilesBackup->getAllFiles());

        $FilesBackup = new FilesBackup(APP, ['git_ignore' => false]);
        $result = $FilesBackup->getAllFiles();
        $this->assertCount($countExpectedFiles + 2, $result);
        $this->assertContains(APP . 'logs' . DS . 'error.log', $result);
        $this->assertContains(APP . 'vendor' . DS . 'vendor.php', $result);

        $FilesBackup = new FilesBackup(APP, ['exclude' => 'subDir' . DS . 'subSubDir']);
        $result = $FilesBackup->getAllFiles();
        $this->assertCount($countExpectedFiles - 1, $result);
        $this->assertNotContains(APP . 'logs' . DS . 'error.log', $result);
        $this->assertNotContains(APP . 'vendor' . DS . 'vendor.php', $result);
        $this->assertNotContains(APP . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile', $result);

        $FilesBackup = new FilesBackup(APP, ['exclude' => ['subDir' . DS . 'subSubDir', 'subDir' . DS . 'anotherSubDir']]);
        $result = $FilesBackup->getAllFiles();
        $this->assertCount($countExpectedFiles - 2, $result);
        $this->assertNotContains(APP . 'logs' . DS . 'error.log', $result);
        $this->assertNotContains(APP . 'vendor' . DS . 'vendor.php', $result);
        $this->assertNotContains(APP . 'subDir' . DS . 'anotherSubDir' . DS . 'anotherSubDirFile', $result);
        $this->assertNotContains(APP . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile', $result);

        $FilesBackup = new FilesBackup(APP, ['include' => 'vendor']);
        $result = $FilesBackup->getAllFiles();
        $this->assertCount($countExpectedFiles + 1, $result);
        $this->assertNotContains(APP . 'logs' . DS . 'error.log', $result);
        $this->assertContains(APP . 'vendor' . DS . 'vendor.php', $result);

        $FilesBackup = new FilesBackup(APP, ['include' => ['logs', 'vendor']]);
        $result = $FilesBackup->getAllFiles();
        $this->assertCount($countExpectedFiles + 2, $result);
        $this->assertContains(APP . 'logs' . DS . 'error.log', $result);
        $this->assertContains(APP . 'vendor' . DS . 'vendor.php', $result);
    }
}
