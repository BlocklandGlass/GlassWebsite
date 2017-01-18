<?php
require dirname(__DIR__) . '/autoload.php';

use Glass\RepositoryChecker;
use Glass\RepositoryManager;

header('Content-Type: text/json');

ob_start();

RepositoryManager::checkAllRepositories();

$str = ob_get_contents();
// Clean (erase) the output buffer and turn off output buffering
ob_end_clean();
// Write final string to file
file_put_contents(dirname(__FILE__) . "/repoquery.log", $str);
 ?>
