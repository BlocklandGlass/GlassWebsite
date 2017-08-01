<?php
	require dirname(__DIR__) . '/../../private/autoload.php';

  use Glass\AddonManager;
  use Glass\BugManager;
  use Glass\UserManager;
  use Glass\UserLog;

	require_once(realpath(dirname(__DIR__) . "/../../private/lib/Parsedown.php"));

	if(isset($_GET['id'])) {
		try {
      $bug         = BugManager::getFromId($_GET['id']);
			$addonObject = AddonManager::getFromId($bug->aid);
      $comments    = BugManager::getCommentsFromId($bug->id);
		} catch(Exception $e) {
			//board doesn't exist
			header('Location: /addons');
			die("addon doesnt exist");
		}
	} else {
		header('Location: /addons');
		die();
	}

  $body  = trim($_POST['body']  ?? "");

  $invalid = false;
  $user = UserManager::getCurrent();
  if(!$user) {
    header('Location: /login.php?rd=' . $_SERVER['REQUEST_URI']);
    return;
  }

  if($_POST ?? false && !$_SESSION['form_submitted']) {
    if(strlen($body) < 5) {
      $bodyMessage = "Invalid body!<br />";
      $invalid = true;
    }

    if(!$invalid) {
      BugManager::newBugReply($bug->id, $user->getBLID(), $body);
      header("Location: /addons/bugs/view.php?id={$bug->id}");
      return;
    }
    $_SESSION['form_submitted'] = true;
  } else {
    $_SESSION['form_submitted'] = false;
  }

  if($user)
    $vote = BugManager::getVote($bug->id, $user->getBLID());
  else
    $vote = 0;

  $votes  = BugManager::getVotes($bug->id);

	$_PAGETITLE = "Blockland Glass | Bugs";

	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
?>

<script type="text/javascript">
  var vote = '<?php echo $vote ?>';
  var bugId = '<?php echo $bug->id ?>';

  function upvote() {
    $(".upvote").css('color', 'rgba(84, 217, 140, 0.5)');
    $(".downvote").css('color', '#ccc');
    if(vote != 1) {
      var votes = parseInt($("#vote-count").html());
      if(vote == 0)
        $("#vote-count").html(votes+1);
      else if(vote == -1)
        $("#vote-count").html(votes+2);
    }
    vote = 1;

    $.ajax('vote.php?id=' + bugId + '&vote=1&ajax=1');
  }

  function downvote() {
    $(".downvote").css('color', 'rgba(237, 118, 105, 0.5)');
    $(".upvote").css('color', '#ccc');

    if(vote != -1) {
      var votes = parseInt($("#vote-count").html());
      if(vote == 0)
        $("#vote-count").html(votes-1);
      else if(vote == 1)
        $("#vote-count").html(votes-2);
    }
    vote = -1;

    $.ajax('vote.php?id=' + bugId + '&vote=-1&ajax=1');
  }

  $(document).ready(function() {
    if(vote == 1) {
      $(".upvote").css('color', 'rgba(84, 217, 140, 0.5)');
    } else if(vote == -1) {
      $(".downvote").css('color', 'rgba(237, 118, 105, 0.5)');
    }

    $("#upvote-link").attr('href', 'javascript:upvote();');
    $("#downvote-link").attr('href', 'javascript:downvote();');
  })

  function markDuplicate() {

  }
</script>

<style>
  .upvote, .downvote{
    color: #ccc;
  }
  .upvote:hover {
    color: rgba(84, 217, 140, 0.7) !important;
  }
  .downvote:hover {
    color: rgba(237, 118, 105, 0.7) !important;
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="maincontainer">

  <span style="font-size: 0.8em; padding-left: 10px">
    <a href="/addons/addon.php?id=<?php echo $addonObject->getId();?>"><?php echo $addonObject->getName(); ?></a> >> <a href="/addons/bugs?id=<?php echo $addonObject->getId();?>">Bugs</a> >> <b>Bug #<?php echo $bug->id ?></b>
  </span>

  <div class="tile">
    <div>
      <div style="display: inline-block; vertical-align: top;">
        <div style="display: flex; flex-flow: column; margin-right: 20px; margin-left: 10px;">
          <div style="flex: 1; flex-grow: 1; text-align: center">
            <a id="upvote-link" href="vote.php?id=<?php echo $bug->id ?>&vote=1">
              <i id="upvote" class="fa fa-chevron-up upvote" style="font-size: 1.7em; text-align:center;"></i>
            </a>
          </div>
          <div id="vote-count" style="flex: 2; flex-grow: 1; text-align: center; font-size: 2em; color:#83c3f3">
            <?php echo $votes ?>
          </div>
          <div style="flex: 1; flex-grow: 1; text-align: center">
            <a id="downvote-link" href="vote.php?id=<?php echo $bug->id ?>&vote=0">
              <i class="fa fa-chevron-down downvote" style="font-size: 1.7em; text-align:center;"></i>
            </a>
          </div>
        </div>
      </div>
      <div style="display: inline-block">
        <h2 style="margin-top:0; padding-top:0; display: inline-block"><?php echo $addonObject->getName(); ?> <span style="color: #bbb">#<?php echo $bug->id;?></span></h2>
        <?php

        if(!$bug->open) {
          echo '<span style="color: #eee; background-color: #ed7669; padding: 5px; border-radius: 3px;">Closed</span>';
        }

        ?>
        <h3><?php echo $bug->title; ?></h3>
        <p>
          <?php echo $bug->body; ?>
        </p>
      </div>
    </div>
    <?php
      if($user && $addonObject->getManagerBLID() == $user->getBLID() && $bug->open) {
      ?>
      <div style="text-align: right">
        <a href="javascript:markDuplicate();" class="btn yellow" style="font-size: 0.8em; padding: 5px 10px; margin: 5px; display: none">Mark Duplicate</a>
        <a href="close.php?id=<?php echo $bug->id ?>" class="btn red" style="font-size: 0.8em; padding: 5px 10px; margin: 5px">Close Issue</a>
        <div style="text-align: right; display:none;">
          <input style="display: inline-block; margin: 5px font-size: 0.8em" id="duplicate-input" type="text" />
        </div>
      </div>
      <?php
      }
    ?>
  </div>

  <style>
    .commentBox {
      border-collapse: collapse;
      width: 100%;
    }

    .commentBox td {
      font-size: 0.95em;
      padding: 10px;
      border-bottom: 1px solid #bbb;
    }

    .commentBox td:first-of-type {
      font-weight: bold;
      background-color: rgba(84, 217, 140, 0.3);
    }
  </style>

  <div class="tile" style="padding:10px">
    <div style="background-color: rgba(255,255,255,0.4); border-radius:5px">
      <form method="post">
        <table class="commentBox">
          <tbody>
          <?php

          foreach($comments as $comment) {

            ?>
            <tr>
              <td style="width: 20%">
                <?php
                  $name = UserLog::getCurrentUsername($comment->blid);
                  if(!$name)
                    $name = "Blockhead" . $comment->blid;

                  $manager = ($comment->blid == $addonObject->getManagerBLID());

                  if($manager) {
                    echo '<span style="color: red">' . $name . '</span>';
                    echo '<br />';
                    echo '<span style="color: #666; font-size: 0.8em">Developer</span><br />';
                  } else {
                    echo $name;
                  }
                  ?>
                <br />
                <div style="display:inline-block; text-align: left; margin-top:50px; font-weight:nomral; font-size:0.8em;color:#666;">
                  <?php echo $comment->timestamp ?>
                </div>
              </td>
              <td style="vertical-align: top">
                <?php echo $comment->body ?>
              </td>
            </tr>

            <?php

          }



          if($user && $bug->open) {
          ?>
          <tr>
            <td style="width:20%; vertical-align: top; padding: 20px; text-align: right">
              <b>New Comment</b>
            </td>
            <td style="text-align: center">
              <textarea name="body" style="display:block; width:calc(100% - 30px); height: 170px;"></textarea>
              <input type="submit" />
            </td>
          </tr>
          <?php
          }
          ?>
        </tbody>
      </table>
    </form>
  </div>
</div>
