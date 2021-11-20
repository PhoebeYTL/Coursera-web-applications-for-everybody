<!--
index.php Will present a list of all profiles in the system with a link to a detailed view with view.php whether or not you are logged in. 
If you are not logged in, you will be given a link to login.php. If you are logged in you will see a link to add.php add a new resume and 
links to delete or edit any resumes that are owned by the logged in user.
-->

<?php
session_start();
require_once "pdo.php";
require_once "util.php";

//Retieve Profiles from database
$stmt = $pdo->query("SELECT * FROM Profile");
$count = $stmt->rowCount();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Yuen Ting Lai</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Chuck Severance's Resume Registry</h1>

<?php
/*Flash messages*/
flashMessages();

/*Login*/
if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    echo("<p><a href='login.php'>Please log in</a></p>\n");
}

/*Display table*/

if ($count > 0) {
    echo("<table border='1'>\n");
    echo("<tr>\n");
    echo("<th>Name</th>\n");
    echo("<th>Headline</th>\n");
    if (isset($_SESSION['user_id'])) { 
        echo("<th>Action</th>\n");
    }
    echo("</tr>\n");
    foreach ( $rows as $row ) {
        echo("<tr>\n");
        echo("<td>\n");
        echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name'])." ".htmlentities($row['last_name']).'</a>');
        echo("</td>\n");
        echo("<td>\n");
        echo(htmlentities($row['headline']));
        echo("</td>\n");
        if (isset($_SESSION['user_id']) && ($row['user_id']===$_SESSION['user_id'])) { 
            echo("<td>\n");
            echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
            echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>'."\n");
            echo("</td>\n");
        }
        echo("</tr>\n");
    }
    echo("</table>\n");

} else {
    
    echo("<p>No rows found.</p>\n");

}

if (isset($_SESSION['user_id'])) { 
    echo("<p><a href='add.php'>Add New Entry</a></p>\n");
    echo("<p><a href='logout.php'>Logout</a></p>\n");
}

?>

</div>
</body>
</html>

