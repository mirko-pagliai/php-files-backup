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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tools\Event\Event;

/**
 * FilesBackupCommandSubscriber
 */
class FilesBackupCommandSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected OutputInterface $output;

    /**
     * Construct
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output instance
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     * The array keys are event names and the value are the method name to call.
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'FilesBackup.fileAdded' => 'onFileAdded',
            'FilesBackup.zipClosed' => 'onZipClosed',
            'FilesBackup.zipOpened' => 'onZipOpened',
        ];
    }

    /**
     * `onFileAdded`.
     *
     * This event is triggered when a file is added to the backup.
     * @param \Tools\Event\Event $event Event instance
     * @return void
     * @throws \Tools\Exception\KeyNotExistsException
     */
    public function onFileAdded(Event $event): void
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln('Added file: `' . $event->getArg(0) . '`');
        }
    }

    /**
     * `onZipClosed`.
     *
     * This event is triggered when the zip file is closed.
     * @param \Tools\Event\Event $event Event instance
     * @return void
     * @throws \Tools\Exception\KeyNotExistsException
     */
    public function onZipClosed(Event $event): void
    {
        $this->output->writeln('Closed zip file: `' . $event->getArg(0) . '`');
    }

    /**
     * `onFileAdded`.
     *
     * This event is triggered when the zip file is opened.
     * @param \Tools\Event\Event $event Event instance
     * @return void
     * @throws \Tools\Exception\KeyNotExistsException
     */
    public function onZipOpened(Event $event): void
    {
        $this->output->writeln('Opened zip file: `' . $event->getArg(0) . '`');
    }
}
