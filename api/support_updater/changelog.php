<?php
header('Content-Type: text/plain');
require_once '../../mysql.php';
require "../../script/SemVer/Version/Versionable.php";
require "../../script/SemVer/Version/PreRelease.php";
require "../../script/SemVer/Version/Build.php";

require "../../script/SemVer/Parser/Versionable.php";
require "../../script/SemVer/Parser/PreRelease.php";
require "../../script/SemVer/Parser/Build.php";
foreach (glob("../../script/SemVer/*.php") as $filename) {
    require $filename;
}
use Naneau\SemVer\Sort;

$mysqli = OpenMysqliCon();
if(!isset($_GET['id'])) {
    $_GET['id'] = 1;
}

$branch = $mysqli->real_escape_string($_GET['branch']);

$addonResult = $mysqli->query("SELECT * FROM `addon_addons` WHERE id=" . $_GET['id']);
$addonObj = $addonResult->fetch_object();

$fileResult = $mysqli->query("SELECT * FROM `addon_files` WHERE id=" . $addonObj->file_stable);
$fileObj = $fileResult->fetch_object();

$updateResult = $mysqli->query("SELECT * FROM `addon_updates` WHERE aid=" . $addonObj->id);
$updateArray = array();
while($up = $updateResult->fetch_object()) {
    if($up->branch == $branch) {
        $updateArray[$up->version] = $up;
        $updates[] = $up->version;
    }
}

$updates = Sort::sort($updates);

$updates = array_reverse($updates);

$object = array();
foreach($updates as $update) {
    $up = $updateArray[$update->getOriginalVersion()];

    echo "<version:" . $up->version . ">\n";
    echo $up->changelog;
    echo "\n</version>\n";
}
return;
?>
<version:1.8>
        <ul>
                <li>Added stuff.</li>
                <li>Changed some stuff.</li>
        </ul>
</version>
<version:1.7.5>
        <ul>
                <li>Some other changes.</li>
                <ol>
                        <li>It's an ordered list inside an unordered one!</li>
                </ol>
        </ul>
</version>
