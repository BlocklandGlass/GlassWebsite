<?php
//On the BLF, a lot of topics just link to the addon's /addon.php page
// I don't want all those links to be broken
// so this is purely a redirect
header('Location: /addons/addon.php?id=' . $_REQUEST['id']);
?>
