<?php
require_once dirname(__FILE__) . '/NotificationManager.php';
class ForumTracker {
  private static $boards = [1, 28, 29, 9, 34];

  public static function getNew() {
    foreach(ForumTracker::$boards as $board) {
      ForumTracker::getNewBoard($board);
    }
  }

  public static function getNewBoard($bid) {
    $url = "https://forum.blockland.us/index.php?board=$bid.0";
    // we only need to check one page
    // I doubt there'll be that many updates in a minute
    $html = file_get_contents($url);
    $dom = new DOMDocument;
    $dom->loadHTML($html);

    $tables = $dom->getElementsByTagName("table");
    $header = $tables->item(0);
    $welcome = $header->getElementsByTagName("td")->item(1);

    date_default_timezone_set('US/Eastern');

    $text = $dom->saveHTML($welcome);
    $lines = explode("<br>", $text);
    $words = explode(" ", $lines[2]);
    $date = str_replace($words[2] . " ", "", $lines[2]);
    echo "\"" . $date . "\"<hr />";
    $forumTime = strtotime(strip_tags($date));

    $table = null;
    foreach($tables as $tab) {
      if($tab->getAttribute("class") == "bordercolor") {
        $table = $tab;
      }
    }

    $nlist = $table->getElementsByTagName("tr");
    foreach($nlist as $index=>$tr) {
      if($index == 0) {
        continue;
      }
      $tds = $tr->getElementsByTagName("td");
      $name = "";
      $topic = "";
      foreach($tds as $idx=>$td) {
        if($idx == 1) {
          $u = $td->getElementsByTagName("a")->item(0);
          $name = $u->textContent;
          $topic = str_replace("https://forum.blockland.us/index.php?topic=", "", $u->getAttribute("href"));
        } else if($idx == 5) {
          $text = $dom->saveHTML($td);
          $lines = explode("<br>", $text);

          $date = strip_tags($lines[0]);
          $date = str_replace("Today at", date("F j,", $forumTime), $date);
          $time = strtotime($date);
          $author = strip_tags(substr($lines[1], 3, strlen($lines[1])));

          if($time > $forumTime-60) {
            echo $topic . "\n";
            NotificationManager::sendPushNotification("9789", "Forum Update", "The topic <font:verdana bold:13>" . $name . "<font:verdana:13> has been updated!", "newspaper", "", 0);
          }
        }
      }
    }
  }
}
