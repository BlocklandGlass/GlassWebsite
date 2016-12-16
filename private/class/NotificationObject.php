<?php
namespace Glass;

class NotificationObject {
	//public fields will automatically be put into json
	public $user;
	public $id;
	public $date;
	public $params;
  public $text;

	public function __construct($resource) {
		$this->user = @intval($resource->blid);
		$this->id = @intval($resource->id);
		$this->params = @json_decode($resource->params);
		$this->text = @$resource->text;
    $this->date = @$resource->date;
	}

  public function testVars() {
    $this->params = json_decode('{
      "vars": [
      {"type":"user", "blid":9789},
      {"type":"addon", "id":2}
      ]
    }');
    $this->text = '$1 liked $2';
    $this->date = "2015-11-15 13:37:19";
  }

	public function getId() {
		return $this->id;
	}

  public function getDate() {
    return $this->date;
  }

  public function toHTML() {
    $returnHtml = $this->text;
		if(isset($this->params->vars)) {
	    foreach($this->params->vars as $i => $var) {
	      $html = "<s>invalid</s>";
	      switch($var->type) {
	        case "user":
	          $user = UserManager::getFromBLID($var->blid);
	          $html = "<a href=\"/user/view.php?blid=" . $var->blid . "\">" . $user->getUserName() . "</a>";
	          break;

	        case "addon":
	          $addon = AddonManager::getFromID($var->id);
						if($addon) {
	          	$html = "<a href=\"/addons/addon.php?id=" . $var->id . "\">" . $addon->getName() . "</a>";
						} else {
	          	$html = "<a href=\"/addons/addon.php?id=" . $var->id . "\">{error}</a>";
						}
	          break;
	      }
	      $returnHtml = str_replace('$' . ($i+1), $html, $returnHtml);
	    }
		}

    return $returnHtml;
  }
}
?>
