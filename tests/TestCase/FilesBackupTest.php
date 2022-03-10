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
namespace FilesBackup\Test\TestCase;

use Cake\TestSuite\TestCase;
use FilesBackup\FilesBackup;
use FilesBackup\Test\ZipperReader;

/**
 * FilesBackupTest class
 */
class FilesBackupTest extends TestCase
{
    /**
     * Test for `create()` method
     * @test
     */
    public function testCreate(): void
    {
        $FileExplorer = new FilesBackup(APP);
        $target = $FileExplorer->create(TMP . 'tmp_' . mt_rand() . '.zip');
        $this->assertFileExists($target);

        $Zipper = new ZipperReader($target);
        $files = $Zipper->list();
        $this->assertContains('TestApp' . DS . 'example.php', $files);
        $this->assertContains('TestApp' . DS . 'empty', $files);
        $this->assertContains('TestApp' . DS . '400x400.jpeg', $files);

        unlink($target);
    }

    /**
     * Test for `getAllFiles()` method
     * @test
     */
    public function testGetAllFiles(): void
    {
        $FileExplorer = new FilesBackup(APP);
        $files = $FileExplorer->getAllFiles();
        $this->assertContains(APP . 'example.php', $files);
        $this->assertContains(APP . 'empty', $files);
        $this->assertContains(APP . '400x400.jpeg', $files);
        $this->assertNotContains(APP . 'vendor/vendor.php', $files);

        $FileExplorer = new FilesBackup(APP, ['git_ignore' => false]);
        $files = $FileExplorer->getAllFiles();
        $this->assertContains(APP . 'vendor/vendor.php', $files);
    }
}
