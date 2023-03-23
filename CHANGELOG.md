## 1.0 branch
### 1.0.3
* updated for php-tools 1.7.1.

### 1.0.2-beta3
* command writes number of added files;
* added `--debug` options for `FilesBackupCommand`;
* added tests for PHP 8.1.

### 1.0.1-beta2
* added `include` option for `FilesBackup` instances and `--include` option for
    `FilesBackupCommand`;
* no longer ignores hidden files by default;
* added `FilesBackupCommandSubscriber` class, with command methods for events;
* fixed little bug for `FilesBackup::getAllFiles()` method.

### 1.0.0-beta1
* first release.
