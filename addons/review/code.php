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
var isReviewer = <?php echo ($review ? 1 : 0) ?>;
var root = null;

function openPopup(title, body) {
	$("#popupTitle").html(title);
	$("#popupBody").html(body);

	$(".popupOverlay").css("display", "block");
	$(".popupOverlay").animate({
    opacity: 1,
  }, 200);
}

function closePopup(animate) {
	if(animate !== true) {
		$(".popupOverlay").css("display", "none");
		$(".popupOverlay").css("opacity", "0");
	} else {
		$(".popupOverlay").animate({
	    opacity: 0
	  }, 200, function() {
			$(".popupOverlay").css("display", "none");
		});
	}
}

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
	var html = "";
	html += '<img src="/img/icons16/database_gear.png" /> <a onclick="loadOverview();" href="#">Overview</a><hr />';
	html += buildFolderHTML(root)
  $('.fileNav').html(html);
}

function renderFile(data) {
  var html = "<h3>" + data.file + "</h3>";
  if(data.source != null) {
		html += '<pre>';
		lines = data.source.split('\n');
		for(i in lines) {
			var line = lines[i];
			if(line.trim().length == 0) {
				line = " ";
			}

			var rand = Math.floor((Math.random() * 10) + 1);
			var comment = rand == 7;

			html += '<span class="line' + (comment ? " linecomment" : "") +'" id="line_' + i + '">' + line + '</span>';
		}
		html += "</pre>";
  } else if(data.image === true) {
		html += '<div class="roundedBox"><img src="/ajax/code/getZipFile.php?id=' + addonId + '&file=' + data.file + '" /></div>';
	} else {
    html += '<div class="message roundedBox">' + data.message + '<br /><br />'
		html += '<a href="/ajax/code/getZipFile.php?id=' + addonId + '&file=' + data.file + '">View/Download Raw File</a>'
		html += '</div>'
  }

  $('.maincontainer').html(html);
}

function renderOverview(data) {
	var html = "";

	html += "<h2>" + data.title + "</h2> by <b>" + data.authorName + "<b><br />";
	html += data.filename;

	$('.maincontainer').html(html);
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
        renderFileNav();
				loadOverview();
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

function loadOverview() {
  $.ajax({
    type: "GET",
    url: "/ajax/code/getOverview.php?id=" + addonId,
    success: function(data) {
      if(data.status == "success") {
        renderOverview(data);
      } else {
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

	$(document).on('click', '.line', function() {
		var lineNumber = this.id.substr(5)
		if($(this).hasClass('linecomment')) {
	    openPopup("Line " + lineNumber + " Comment", '\"You use eval here in a publically accessible function. Don\'t do this\"<br />- <b>Jincux</b>');
		} else {
			var body = "Commenting on line " + lineNumber;
			body += '<br /><div style="text-align:center; margin-top: 10px;">';
			body += '<textarea style="width:270px; height: 100px;"></textarea><br />';
			body += '<button>Post</button></div>';
	    openPopup("Write Comment", body);
		}
	});

	$("#popupClose").click(function() {
		closePopup(true);
	})

  loadFileTree();
});

</script>
<style>
textarea {
	font-size: 10pt;
}

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

.roundedBox {
  background-color: rgba(0, 0, 0, 0.5);

  color: #ffffff;

  padding: 15px;
  border-radius: 5px;
}

pre {
  background-color: rgba(0, 0, 0, 0.5);

  color: #ffffff;

  padding: 15px;
  border-radius: 5px;

	font-family: "Lucida Console", Monaco, monospace;
	font-size: 10pt;
}

.line {
	display: block;
}

.linecomment {
	background-color: rgba(255, 100, 100, 0.5);
	display: block;
}

.line:hover {
	background-color: rgba(255, 255, 255, 0.1);
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

.mu_conditional {
	color: rgb(130, 170, 255);
}

.mu_object {
	color: rgb(255, 120, 170);
}

.review {
	background-color: rgba(220, 210, 200, 0.9);
	position: fixed;

	width: 500px;

	bottom: 10px;
	left: 50%;
	margin-left: -250px; /* Negative half of width. */
	padding: 20px;
	border-radius: 15px;
}

.acceptButton {
	background-color: rgb(90, 220, 100);
	padding: 5px;
	vertical-align: middle;
	border-radius: 5px;

	font-size: 10pt;
	font-weight: bold;
}

.acceptButton:hover {
	color: #eee;
	background-color: rgb(150, 255, 150);
}

.rejectButton {
	background-color: rgb(255, 100, 100);
	padding: 5px;
	vertical-align: middle;
	border-radius: 5px;

	font-size: 10pt;
	font-weight: bold;
}

.rejectButton:hover {
	color: #eee;
	background-color: rgb(255, 150, 150);
}

.popup {
	width: 300px;
	height: 300px;
	top: 50%;
	left: 50%;
	margin-left: -150px; /* Negative half of width. */
	margin-top: -150px; /* Negative half of width. */

	background-color: #eeeeee;
	border: 1px solid #cecece;
	border-radius: 15px;

	padding: 15px;

	position: fixed;
}

.popupOverlay {
	background-color: rgba(0, 0, 0, 0.7);
	position: fixed;
	top: 0;
	left: 0;
	height: 100%;
	width: 100%;

	z-index: 999999;

	display: none;
}

#popupClose {
	float: right;
	font-size: 30px;
	font-weight: bold;

	margin: 0;
	padding: 0;

	text-decoration: none;

	color: #ccc;
}

#popupClose:hover {
	color: #333;
}

</style>
<div class="fileNav">
</div>
<div class="maincontainer">
</div>
<div class="review">
	<b>Review Options</b>
	<hr />
	<a class="acceptButton"><img src="/img/icons16/accept_button.png" /> Accept</a>
	<a class="rejectButton"><img src="/img/icons16/delete.png" /> Reject</a>
</div>
<div class="popupOverlay">
	<div class="popup">
		<a id="popupClose" href="#">X</a>
		<h2 id="popupTitle">Popup Title</h2>
		<hr />
		<div id="popupBody">Pop-up body</div>
	</div>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
