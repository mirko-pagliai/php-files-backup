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

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__) . DS);;
define('TESTS', ROOT . 'tests' . DS);
define('TEST_APP', TESTS . 'test_app' . DS);
define('APP', TEST_APP . 'TestApp' . DS);
define('APP_DIR', 'test_app');
define('TMP', sys_get_temp_dir() . DS . 'php-files-backup' . DS);
define('CONFIG', APP . 'config' . DS);

@mkdir(TMP, 0777, true);

require dirname(__DIR__) . '/vendor/autoload.php';
