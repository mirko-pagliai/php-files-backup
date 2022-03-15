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
use Symfony\Component\Console\Tester\CommandTester;
use Tools\TestSuite\TestCase;

/**
 * FilesBackupCommandTest class
 */
class FilesBackupCommandTest extends TestCase
{
    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $target = TMP . 'tmp_' . mt_rand() . '.zip';

        $command = new FilesBackupCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(compact('target'));
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Source: ' . APP, $output);
        $this->assertStringContainsString('Target: ' . $target, $output);
        $this->assertStringContainsString('Backup exported successfully to `' . $target . '`', $output);
    }
}
