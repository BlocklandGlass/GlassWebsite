<?php
namespace Glass;

class InstallationManager {
  public static $unix_modules = ['apc', 'calendar', 'Core', 'ctype', 'curl', 'date', 'dom', 'exif', 'fileinfo', 'filter', 'ftp', 'geoip', 'gettext', 'hash', 'iconv', 'json', 'libxml', 'mbstring', 'mcrypt', 'mysqli', 'mysqlnd', 'openssl', 'pcre', 'PDO', 'pdo_mysql', 'Phar', 'posix', 'readline', 'Reflection', 'session', 'shmop', 'SimpleXML', 'sockets', 'SPL', 'standard', 'sysvmsg', 'sysvsem', 'sysvshm', 'tokenizer', 'wddx', 'xml', 'xmlreader', 'xmlwriter', 'xsl', 'zip', 'zlib'];

  public static $win_modules = ['apcu', 'calendar', 'Core', 'ctype', 'curl', 'date', 'dom', 'exif', 'fileinfo', 'filter', 'ftp', 'geoip', 'gettext', 'hash', 'iconv', 'json', 'libxml', 'mbstring', 'mysqli', 'mysqlnd', 'openssl', 'pcre', 'PDO', 'pdo_mysql', 'Phar', 'readline', 'Reflection', 'session', 'shmop', 'SimpleXML', 'sockets', 'SPL', 'standard', 'tokenizer', 'wddx', 'xml', 'xmlreader', 'xmlwriter', 'xsl', 'zip', 'zlib'];

  public static function isWindows() {
    if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      return true;
    }

    return false;
  }

  public static function checkInstallation() {
    if(!is_file( dirname(__DIR__) . '/config.json' )) {
      return false;
    }

    return true;
  }

  public static function getModules() {
    return $modules;
  }

  public static function getModuleStatus() {
    $returnArray = [];
    $installed = get_loaded_extensions();

    if(InstallationManager::isWindows()) {
      foreach(InstallationManager::$win_modules as $mod) {
        $returnArray[$mod] = in_array($mod, $installed);
      }
    } else {
      foreach(InstallationManager::$unix_modules as $mod) {
        $returnArray[$mod] = in_array($mod, $installed);
      }
    }

    return $returnArray;
  }
}
?>
