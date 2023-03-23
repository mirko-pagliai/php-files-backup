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
namespace FilesBackup\Test\TestCase\Command;

use FilesBackup\Command\FilesBackupCommand;
use FilesBackup\TestSuite\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Tools\TestSuite\Console\CommandTester;

/**
 * FilesBackupCommandTest class
 */
class FilesBackupCommandTest extends TestCase
{
    /**
     * @uses \FilesBackup\Command\FilesBackupCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $expectedFiles = $this->getExpectedFiles(true);
        $target = TMP . 'tmp_' . mt_rand() . '.zip';

        $CommandTester = new CommandTester(new FilesBackupCommand());
        $CommandTester->execute(compact('target'), ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $CommandTester->assertCommandIsSuccessful();
        $CommandTester->assertOutputContains('Source: `' . APP . '`');
        $CommandTester->assertOutputContains('Target: `' . $target . '`');
        $CommandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $CommandTester->assertOutputContains('Opened zip file: `' . $target . '`');
        foreach ($expectedFiles as $filename) {
            $CommandTester->assertOutputContains('Added file: `' . $filename . '`');
        }
        $CommandTester->assertOutputContains('Closed zip file: `' . $target . '`');
        $CommandTester->assertOutputContains('Backup exported successfully to: `' . $target . '`');
        $CommandTester->assertOutputContains('File added: ' . (count($expectedFiles) + 1));

        @unlink($target);

        //With `--no-git-ignore` option
        $CommandTester->execute(compact('target') + ['--no-git-ignore' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $CommandTester->assertCommandIsSuccessful();
        $CommandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will not be ignored');
        $CommandTester->assertOutputContains('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`');
        $CommandTester->assertOutputContains('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`');
        $CommandTester->assertOutputContains('File added: ' . (count($expectedFiles) + 3));

        @unlink($target);

        //With `--exclude` option
        $CommandTester->execute(compact('target') + ['--exclude' => 'subDir' . DS . 'subSubDir'], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $CommandTester->assertCommandIsSuccessful();
        $CommandTester->assertOutputContains('Excluded directories: `subDir' . DS . 'subSubDir`');
        $CommandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $CommandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile`');
        $CommandTester->assertOutputContains('File added: ' . count($expectedFiles));

        @unlink($target);

        //With `--exclude` option as array
        $CommandTester->execute(compact('target') + ['--exclude' => ['subDir' . DS . 'subSubDir', 'subDir' . DS . 'anotherSubDir']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $CommandTester->assertCommandIsSuccessful();
        $CommandTester->assertOutputContains('Excluded directories: `subDir' . DS . 'subSubDir`, `subDir' . DS . 'anotherSubDir`');
        $CommandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $CommandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile`');
        $CommandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'subDir' . DS . 'anotherSubDir' . DS . 'anotherSubDirFile`');
        $CommandTester->assertOutputContains('File added: ' . (count($expectedFiles) - 1));

        @unlink($target);

        //With `--include` option
        $CommandTester->execute(compact('target') + ['--include' => 'vendor'], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $CommandTester->assertCommandIsSuccessful();
        $CommandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $CommandTester->assertOutputContains('Included directories: `vendor`');
        $CommandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`');
        $CommandTester->assertOutputContains('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`');
        $CommandTester->assertOutputContains('File added: ' . (count($expectedFiles) + 2));

        @unlink($target);

        //With `--include` option as array
        $CommandTester->execute(compact('target') + ['--include' => ['logs', 'vendor']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $CommandTester->assertCommandIsSuccessful();
        $CommandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $CommandTester->assertOutputContains('Included directories: `logs`, `vendor`');
        $CommandTester->assertOutputContains('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`');
        $CommandTester->assertOutputContains('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`');
        $CommandTester->assertOutputContains('File added: ' . (count($expectedFiles) + 3));

        //On failure
        $target = TMP . 'noExisting' . DS . 'file.zip';
        $CommandTester->execute(compact('target'));
        $CommandTester->assertCommandIsFailure();
        $CommandTester->assertOutputContains('Error: file or directory `' . dirname($target) . '` is not writable');

        //With `--debug` option
        $this->expectExceptionMessage('File or directory `' . dirname($target) . '` is not writable');
        $CommandTester->execute(compact('target'), ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
    }
}
