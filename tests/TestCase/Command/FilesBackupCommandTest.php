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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

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
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $expectedFiles = $this->getExpectedFiles(true);
        $target = TMP . 'tmp_' . mt_rand() . '.zip';

        $commandTester = $this->getCommandTester();
        $commandTester->execute(compact('target'), ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Source: `' . APP . '`', $output);
        $this->assertStringContainsString('Target: `' . $target . '`', $output);
        $this->assertStringContainsString('The files and directories specified in the `.git_ignore` file will be automatically ignored', $output);
        $this->assertStringContainsString('Opened zip file: `' . $target . '`', $output);
        foreach ($expectedFiles as $filename) {
            $this->assertStringContainsString('Added file: `' . $filename . '`', $output);
        }
        $this->assertStringContainsString('Closed zip file: `' . $target . '`', $output);
        $this->assertStringContainsString('Backup exported successfully to: `' . $target . '`', $output);
        $this->assertStringContainsString('File added: ' . (string)(count($expectedFiles) + 1), $output);

        @unlink($target);

        //With `--no-git-ignore` option
        $commandTester->execute(compact('target') + ['--no-git-ignore' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The files and directories specified in the `.git_ignore` file will not be ignored', $output);
        $this->assertStringContainsString('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`', $output);
        $this->assertStringContainsString('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`', $output);
        $this->assertStringContainsString('File added: ' . (string)(count($expectedFiles) + 3), $output);

        @unlink($target);

        //With `--exclude` option
        $commandTester->execute(compact('target') + ['--exclude' => 'subDir' . DS . 'subSubDir'], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Excluded directories: `subDir' . DS . 'subSubDir`', $output);
        $this->assertStringContainsString('The files and directories specified in the `.git_ignore` file will be automatically ignored', $output);
        $this->assertStringNotContainsString('Added file: `TestApp' . DS . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile`', $output);
        $this->assertStringContainsString('File added: ' . (string)count($expectedFiles), $output);

        @unlink($target);

        //With `--exclude` option as array
        $commandTester->execute(compact('target') + ['--exclude' => ['subDir' . DS . 'subSubDir', 'subDir' . DS . 'anotherSubDir']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Excluded directories: `subDir' . DS . 'subSubDir`, `subDir' . DS . 'anotherSubDir`', $output);
        $this->assertStringContainsString('The files and directories specified in the `.git_ignore` file will be automatically ignored', $output);
        $this->assertStringNotContainsString('Added file: `TestApp' . DS . 'subDir' . DS . 'subSubDir' . DS . 'subSubDirFile`', $output);
        $this->assertStringNotContainsString('Added file: `TestApp' . DS . 'subDir' . DS . 'anotherSubDir' . DS . 'anotherSubDirFile`', $output);
        $this->assertStringContainsString('File added: ' . (string)(count($expectedFiles) - 1), $output);

        @unlink($target);

        //With `--include` option
        $commandTester->execute(compact('target') + ['--include' => 'vendor'], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The files and directories specified in the `.git_ignore` file will be automatically ignored', $output);
        $this->assertStringContainsString('Included directories: `vendor`', $output);
        $this->assertStringNotContainsString('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`', $output);
        $this->assertStringContainsString('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`', $output);
        $this->assertStringContainsString('File added: ' . (string)(count($expectedFiles) + 2), $output);

        @unlink($target);

        //With `--include` option as array
        $commandTester->execute(compact('target') + ['--include' => ['logs', 'vendor']], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The files and directories specified in the `.git_ignore` file will be automatically ignored', $output);
        $this->assertStringContainsString('Included directories: `logs`, `vendor`', $output);
        $this->assertStringContainsString('Added file: `TestApp' . DS . 'logs' . DS . 'error.log`', $output);
        $this->assertStringContainsString('Added file: `TestApp' . DS . 'vendor' . DS . 'vendor.php`', $output);
        $this->assertStringContainsString('File added: ' . (string)(count($expectedFiles) + 3), $output);
    }

    /**
     * Test for `execute()` method on failure
     * @test
     */
    public function testExecuteOnFailure(): void
    {
        $target = TMP . 'noExisting' . DS . 'file.zip';

        $commandTester = $this->getCommandTester();
        $commandTester->execute(compact('target'));
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Error: file or directory `' . dirname($target) . '` does not exist', $output);

        //With `--debug` option
        $this->expectExceptionMessage('File or directory `' . dirname($target) . '` does not exist');
        $commandTester->execute(compact('target'), ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
    }
}
