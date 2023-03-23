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
     * Internal method to get a `CommandTest` instance for the tested command
     * @return CommandTester
     */
    protected function getCommandTester(): CommandTester
    {
        return new CommandTester(new FilesBackupCommand());
    }

    /**
     * @uses \FilesBackup\Command\FilesBackupCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $expectedFiles = $this->getExpectedFiles(true);
        $target = TMP . 'tmp_' . mt_rand() . '.zip';

        $commandTester = $this->getCommandTester();
        $commandTester->execute(compact('target'), ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $commandTester->assertOutputContains('Source: `' . APP . '`');
        $commandTester->assertOutputContains('Target: `' . $target . '`');
        $commandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $commandTester->assertOutputContains('Opened zip file: `' . $target . '`');
        foreach ($expectedFiles as $filename) {
            $commandTester->assertOutputContains('Added file: `' . $filename . '`');
        }
        $commandTester->assertOutputContains('Closed zip file: `' . $target . '`');
        $commandTester->assertOutputContains('Backup exported successfully to: `' . $target . '`');
        $commandTester->assertOutputContains('File added: ' . (string)(count($expectedFiles) + 1));

        @unlink($target);

        //With `--no-git-ignore` option
        $commandTester->execute(compact('target') + ['--no-git-ignore' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $commandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will not be ignored');
        $commandTester->assertOutputContains('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`');
        $commandTester->assertOutputContains('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`');
        $commandTester->assertOutputContains('File added: ' . (string)(count($expectedFiles) + 3));

        @unlink($target);

        //With `--exclude` option
        $commandTester->execute(compact('target') + ['--exclude' => 'subDir' . DS . 'subSubDir'], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $commandTester->assertOutputContains('Excluded directories: `subDir' . DS . 'subSubDir`');
        $commandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $commandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile`');
        $commandTester->assertOutputContains('File added: ' . (string)count($expectedFiles));

        @unlink($target);

        //With `--exclude` option as array
        $commandTester->execute(compact('target') + ['--exclude' => ['subDir' . DS . 'subSubDir', 'subDir' . DS . 'anotherSubDir']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $commandTester->assertOutputContains('Excluded directories: `subDir' . DS . 'subSubDir`, `subDir' . DS . 'anotherSubDir`');
        $commandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $commandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile`');
        $commandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'subDir' . DS . 'anotherSubDir' . DS . 'anotherSubDirFile`');
        $commandTester->assertOutputContains('File added: ' . (string)(count($expectedFiles) - 1));

        @unlink($target);

        //With `--include` option
        $commandTester->execute(compact('target') + ['--include' => 'vendor'], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $commandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $commandTester->assertOutputContains('Included directories: `vendor`');
        $commandTester->assertOutputNotContains('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`');
        $commandTester->assertOutputContains('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`');
        $commandTester->assertOutputContains('File added: ' . (string)(count($expectedFiles) + 2));

        @unlink($target);

        //With `--include` option as array
        $commandTester->execute(compact('target') + ['--include' => ['logs', 'vendor']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $commandTester->assertOutputContains('The files and directories specified in the `.git_ignore` file will be automatically ignored');
        $commandTester->assertOutputContains('Included directories: `logs`, `vendor`');
        $commandTester->assertOutputContains('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`');
        $commandTester->assertOutputContains('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`');
        $commandTester->assertOutputContains('File added: ' . (string)(count($expectedFiles) + 3));
    }

    /**
     * Test for `execute()` method on failure
     * @test
     * @uses \FilesBackup\Command\FilesBackupCommand::execute()
     */
    public function testExecuteOnFailure(): void
    {
        $target = TMP . 'noExisting' . DS . 'file.zip';

        $commandTester = $this->getCommandTester();
        $commandTester->execute(compact('target'));
        $commandTester->assertCommandIsFailure();
        $commandTester->assertOutputContains('Error: file or directory `' . dirname($target) . '` is not writable');

        //With `--debug` option
        $this->expectExceptionMessage('File or directory `' . dirname($target) . '` is not writable');
        $commandTester->execute(compact('target'), ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
    }
}
