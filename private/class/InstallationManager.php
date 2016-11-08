<?php
class InstallationManager {
  public static $modules = ['calendar', 'Core', 'ctype', 'curl', 'date', 'dom', 'exif', 'fileinfo', 'filter', 'ftp', 'geoip', 'gettext', 'hash', 'iconv', 'json', 'libxml', 'mbstring', 'mcrypt', 'mysqli', 'mysqlnd', 'openssl', 'pcre', 'PDO', 'pdo_mysql', 'Phar', 'posix', 'readline', 'Reflection', 'session', 'shmop', 'SimpleXML', 'sockets', 'SPL', 'standard', 'sysvmsg', 'sysvsem', 'sysvshm', 'tokenizer', 'wddx', 'xml', 'xmlreader', 'xmlwriter', 'xsl', 'zip', 'zlib'];

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
    foreach(InstallationManager::$modules as $mod) {
      $returnArray[$mod] = in_array($mod, $installed);
    }
    return $returnArray;
  }
}
?>
