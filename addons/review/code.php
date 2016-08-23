<?php
	$_PAGETITLE = "Glass | Inspect Update";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserLog.php"));

	$user = UserManager::getCurrent();
	$review = false;
	if(is_object($user)) {
		$review = $user->inGroup("Reviewer");
	}

  $addon = AddonManager::getFromID($_REQUEST['id']);
  $manager = UserManager::getFromBLID($addon->getManagerBLID());
?>
<script type="text/javascript">
var addonId = <?php echo $addon->getId(); ?>;
var root = null;

function buildFolderHTML(folder) {
  var html = '<ul class="filelist">';

  for(dir in folder.dirs) {
    var obj = folder.dirs[dir];

    html += '<li>';
    html += '<img src="/img/icons16/folder.png" /> ';

    html += '<a href="javascript:toggleFolder(\'' + obj.abs + '\')">'
    html += dir
    html += '</a>'

    if(obj.open) {
      html += buildFolderHTML(obj);
    }

    html += '</li>';
  }

  var img = [];
  img['cs'] = "script_code_red";
  img['gui'] = "new_window";
  img['txt'] = "document_index";

  img['json'] = "script_green";

  img['png'] = "image";
  img['jpg'] = "image";
  img['jpeg'] = "image";

  img['wav'] = "sound";
  //img['']

  for(i in folder.files) {
    var file = folder.files[i];

    var ext = file.substr(file.lastIndexOf('.')+1).trim();

    if(img[ext] != null)
      icon = img[ext];
    else
      icon = "file_extension_txt";

    html += '<li class="fileli">';
    html += '<img src="/img/icons16/' + icon + '.png" /> ';

    var f;
    if(folder.root) {
      f = file
    } else {
      f = folder.abs + '/' + file
    }

    html += '<a href="javascript:loadFile(\'' + f + '\')">'
    html += file;
    html += '</a>'

    html += '</li>';
  }

  html += '</ul>';

  return html;
}

function toggleFolder(directory) {
  console.log("opening " + directory);
  var dir = getDirectory(directory);

  if(dir.open === true)
    dir.open = false;
  else
    dir.open = true;

  renderFileNav();
}

function renderFileNav() {
  $('.fileNav').html(buildFolderHTML(root));
}

function renderFile(data) {
  $('.maincontainer').html("<h3>" + data.file + "</h3><pre>" + data.source + "</pre>");
}

function getDirectory(directory) {
  return getChildDirectory(root, directory);
}

function getChildDirectory(parent, child) {
  var idx = child.indexOf('/');
  if(idx == -1) {
    console.log("found " + child + " in " + parent.abs)
    console.log(parent.dirs[child]);
    return parent.dirs[child];
  } else {
    var first = child.substr(0, idx);
    return getChildDirectory(parent.dirs[first], child.substr(idx+1));
  }
}

function loadFileTree() {
  $.ajax({
    type: "GET",
    url: "/ajax/code/getFiles.php?id=" + addonId,
    success: function(data) {
      if(data.status == "success") {
        root = data.tree;
        $('.fileNav').html(buildFolderHTML(root));
        loadFile('description.txt')
      } else {
        $('.fileNav').css("background-color", "rgba(150, 0, 0, 0.5)");
        $('.fileNav').html("<pre>" + JSON.stringify(data) + "</pre>");
      }
    }
  });
}

function loadFile(file) {
  $.ajax({
    type: "GET",
    url: "/ajax/code/getFile.php?id=" + addonId + "&file=" + file,
    success: function(data) {
      if(data.status == "success") {
        console.log("loaded file " + file)
        renderFile(data);
      } else {
        alert("uh oh");
        $('.maincontainer').css("background-color", "rgba(150, 0, 0, 0.5)");
      }
    }
  });
}

$(document).ready(function() {
  $(document).scroll(function() {
    var scrollTop = $(document).scrollTop();

    var pos;

    if(scrollTop > 74) {
      pos = 10;
    } else {
      pos = 74-scrollTop;
    }

    $('.fileNav').css('top', pos);
  });
  loadFileTree();
});

</script>
<style>
.maincontainer {
  font-size: 11pt;
}

.fileNav {
  position: fixed;
  left: 10px;
  top: 74px;

  min-width: 300px;
  /*height: 80%;*/

  background-color: rgba(0, 0, 0, 0.5);

  color: #ffffff;

  padding: 15px;
  border-radius: 5px;

  z-index: 9999;
}

.fileNav a {
  color: #ffffff;
  text-decoration: none;
  font-weight: bold;
}

.fileNav a:hover {
  text-decoration: underline;
}

.filelist {
  margin: 0;
  padding: 0;

  padding-left: 15px;
}

.filelist li {
  margin: 0;
  padding: 0;

  list-style: none;
  white-space: nowrap;
}

.fileli > a {
  font-weight: normal;
}

.code {
  font-size: 0.8em;
  background-color: #eee;
  padding: 5px;
  width: 50%;
  vertical-align : top;
  white-space    : pre;
  font-family    : monospace;
}

pre {
  background-color: rgba(0, 0, 0, 0.5);

  color: #ffffff;

  padding: 15px;
  border-radius: 5px;
}

.mu_function {
	color: rgb(255, 200, 100);
}

.mu_exec {
	color: rgb(100, 200, 255);
}

.mu_return {
	color: rgb(255, 200, 200);
}

.mu_global {
	color: rgb(170, 230, 255);
}

.mu_local {
	color: rgb(230, 170, 255);
}

.mu_text {
	color: rgb(200, 200, 200);
}

.mu_new {
	color: rgb(180, 250, 180);
}

.mu_comment {
	color: rgb(50, 180, 110) !important;
}

</style>
<div class="fileNav">
</div>
<div class="maincontainer">
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
