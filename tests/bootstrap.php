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

ini_set('intl.default_locale', 'en_US');
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__) . DS);
}

require ROOT . 'vendor/autoload.php';
require ROOT . 'config/bootstrap.php';

define('TESTS', ROOT . 'tests' . DS);
define('APP', TESTS . 'test_app' . DS . 'TestApp' . DS);
define('TMP', sys_get_temp_dir() . DS . 'php-files-backup' . DS);

@mkdir(TMP, 0777, true);
