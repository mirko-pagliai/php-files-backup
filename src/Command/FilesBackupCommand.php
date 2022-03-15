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
namespace FilesBackup\Command;

use FilesBackup\FilesBackup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * FilesBackupCommand class
 */
class FilesBackupCommand extends Command
{
    /**
     * Name of the command
     * @var string
     */
    protected static $defaultName = 'backup';

    /**
     * Description of the command
     * @var string
     */
    protected static $defaultDescription = 'Performs a files backup';

    /**
     * Command configuration
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp('This command performs a files backup');

        $this->addArgument('target', InputArgument::REQUIRED, 'Target zip file you want to create');

        $defaultSource = defined('APP') ? APP : (defined('ROOT') ? ROOT : getcwd());
        $this->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Source directory', $defaultSource);
    }

    /**
     * Command execution
     * @param \Symfony\Component\Console\Input\InputInterface $input InputInterface
     * @param \Symfony\Component\Console\Output\OutputInterface $output OutputInterface
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $input->getArgument('target');

        $output->writeln('Source: ' . $input->getOption('source'));
        $output->writeln('Target: ' . $target);

        try {
            $FilesBackup = new FilesBackup($input->getOption('source'));
            $FilesBackup->create($target);

            $output->writeln('<info>Backup exported successfully to `' . $target . '`</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . strtolower($e->getMessage()) . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
