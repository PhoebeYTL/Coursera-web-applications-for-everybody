<!--
    delete.php delete an entry from the database. 
    Do not do the delete in a GET - you must put up a verification screen and do the actual delete in a POST request, after which you redirect back to index.php with a success message. 
    Before you do the delete, make sure the user is logged in, that the entry actually exists, and that the current logged in user owns the entry in the database. (delete)
-->

<?php
session_start();
require_once "pdo.php";

// If the user is not log in, access is deny
if (!isset($_SESSION['user_id'])) {
    die('ACCESS DENIED');
}

// If the user press cancel, they go back to index.php
if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

// Validate POST data, update the actual SQL table if passes validation
// POST data will be empty on initial run
if (isset($_POST['delete']) && isset($_POST['profile_id'])) {
    $sql = "DELETE FROM profile WHERE profile_id = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':pid' => $_POST['profile_id']));

    // delete the associate position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array(":pid" => $_REQUEST['profile_id']));

    // delete the assocaite education entries
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
    $stmt->execute(array(":pid" => $_REQUEST['profile_id'])); 

    $_SESSION['success'] = 'Record deleted';
    header('Location: index.php');
    return;
}

// Make sure that profile_id is present (GET that was passed in to the initial URL)
if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}
  
$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :profileid AND user_id = :userid");
$stmt->execute(array(
    ":profileid" => $_GET['profile_id'],
    ":userid" => $_SESSION['user_id'])
);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = 'Either profile_id does not exist OR profile_id does not belong to the current login user.';
    header('Location: index.php') ;
    return;
}

$firstname = htmlentities($row['first_name']);
$lastname = htmlentities($row['last_name']);
$profile_id = $row['profile_id'];
?>


<!DOCTYPE html>
<html>
<head>
<title>Yuen Ting Lai</title>
<?php require_once "bootstrap.php"; ?>
</head>

<body>
<div class="container">

<h1>Deleting Profile</h1>

<?php
    if (isset($_SESSION["error"]) ) {
        echo('<p style="color:red">'.$_SESSION["error"]."</p>\n");
        unset($_SESSION["error"]);
    }
?>

<p>First Name <?= $firstname ?></p>
<p>Last Name <?= $lastname ?></p>

<form method="post">
    <input type="hidden" name="profile_id" value="<?= $profile_id ?>">
    <input type="submit" name="delete" value="Delete">
    <input type="submit" name="cancel" value="Cancel">
</form>

</div>
</body>
</html>
