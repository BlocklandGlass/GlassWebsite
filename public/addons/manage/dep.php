<style>
td {
  padding: 5px;
}

button {
  font-size: 0.6em;
}
</style>

<?php
require dirname(__DIR__) . '/../../private/autoload.php';

if($user === false || ($addon->getManagerBLID() !== $user->getBLID() && !$user->inGroup("Administrator"))) {
  die("You do not have permission to access this area.");
}

use Glass\DependencyManager;
use Glass\AddonManager;

if(isset($_REQUEST['action'])) {
  if($_REQUEST['action'] == "add") {
    DependencyManager::addDependencyByID($_GET['id'], $_REQUEST['aid']);
  } else if($_REQUEST['action'] == "delete") {
    DependencyManager::removeDependencyByAddonID($_GET['id'], $_REQUEST['aid']);
  }
}

$dep = DependencyManager::getDependenciesFromAddonID($_GET['id']);

if(sizeof($dep) == 0) {
  $html = "<strong>No Dependencies!</strong>";
} else {
  $html = "<form action=\"\" method=\"post\"><input type=\"hidden\" name=\"action\" value=\"delete\" />";
  $html .= "<table><tbody><tr><td colspan=\"2\"><strong>Dependencies:</strong></td></tr>";
  foreach($dep as $did) {
    $d = DependencyManager::getFromId($did);
    $html = $html . "<td>" . AddonManager::getFromId($d->getRequired())->getName() . "</td><td><button name=\"aid\" value=\"" . $d->getRequired() . "\">Delete</button></td></tr>";
  }

  $html = $html . "</tbody></table></form>";
}

echo $html;
?>
<hr />
Add: <input type="text" id="addon" />
<form method="post" action="">
  <input type="hidden" name="action" value="add" />
  <div id="options">

  </div>
</form>

<script type="text/javascript">
$("#addon").keyup(function() {
  $.ajax({
    url: "/ajax/searchAddonNames.php?query=" + $("#addon").val()
  }).done(function(data) {
    res = JSON.parse(data);
    var html = "";
    for(i = 0; i < res.length; i++) {
      html = html + res[i].name + " <button name=\"aid\" type=\"submit\" value=\"" + res[i].id + "\">Add</button><br />";
    }
    $("#options").html(html);
  })
});
</script>
