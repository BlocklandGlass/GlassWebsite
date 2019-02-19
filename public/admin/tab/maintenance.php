<h1>Maintenance</h1>

<?php
  use Glass\GroupManager;
  use Glass\DatabaseManager;

	if(!$user->inGroup("Administrator")) {
    die('You do not have permission to access this area.');
  }

  if(isset($_POST['cleanup_comments'])) {
    if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
      throw new \Exception("Cross site request forgery attempt blocked");
    }

    $db = new DatabaseManager();
    $db->query('
      DELETE
        bad_comments.*
      FROM
        `addon_comments` bad_comments
      INNER JOIN(
        SELECT
          `blid`,
          `aid`,
          `comment`,
          MIN(id) min_id,
          COUNT(*)
        FROM
          `addon_comments`
        GROUP BY
          `blid`,
          `aid`,
          `comment`
        HAVING
          COUNT(*) > 1
      ) good_comments
      ON
        good_comments.`blid` = bad_comments.`blid` AND good_comments.`aid` = bad_comments.`aid` AND good_comments.`comment` = bad_comments.`comment` AND good_comments.min_id <> bad_comments.id'
    );

    $error = $db->error();

    if($error != "") {
      throw new \Exception("Database error: " . $error);
    }
  }
?>

<style>
  fieldset {
    border: 1px solid black;
    padding: 1rem;
  }
</style>

<form method="post">
  <fieldset>
    <legend>Duplicate Comments Cleanup</legend>
    <p>The following button will remove all duplicate comments in the database.<br>
    <strong>Duplicate comments are defined as comments which are exactly the same, appear more than once in an add-on page and are made by the same user.</strong><br>
    This will preserve the first comment of any deleted duplicate set of comments.</p>
    <button type="submit" name="cleanup_comments">Cleanup Duplicate Comments</button>
    <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
  </fieldset>
</form>