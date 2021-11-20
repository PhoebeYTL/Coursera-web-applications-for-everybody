<!--
    view.php show the detail for a particular entry. This works even if the user is not logged in. (read)
-->

<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}
  
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header('Location: index.php') ;
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Yuen Ting Lai</title>
<?php require_once "bootstrap.php"; ?>
</head>

<body>
<div class="container">

<h1>Profile information</h1>

<?php
if (isset($_GET['profile_id'])) {
    //echo($_GET['profile_id']);
    $queryString="SELECT * FROM `profile` WHERE profile_id=".$_GET['profile_id'];
    //echo($queryString);
    $stmt = $pdo->query($queryString);
    $count = $stmt->rowCount();
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    if ($count <= 0) {

        $_SESSION['error'] = 'Bad value for profile_id';
        header('Location: index.php');
        return;
    
    } else {
        foreach ($profiles as $profile) {
            $profile_id = $profile['profile_id'];
            $firstname = htmlentities($profile['first_name']);
            $lastname = htmlentities($profile['last_name']);
            $email = htmlentities($profile['email']);
            $headline = htmlentities($profile['headline']);
            $summary = htmlentities($profile['summary']);
            
            $positions = loadPos($pdo, $_REQUEST['profile_id']);
            
            $schools = loadEdu($pdo, $_REQUEST['profile_id']);

            echo("<p>First Name: ".$firstname."</p>");
            echo("<p>Last Name: ".$lastname."</p>");
            echo("<p>Email: ".$email."</p>");
            echo("<p>Headline:<br>".$headline."</p>");
            echo("<p>Summary:<br>".$summary."</p>");

            if (count($positions) > 0) {
                echo("<p>Positions\n");
                echo("<ul>\n");
                foreach ($positions as $position) {
                    $year = htmlentities($position['year']);
                    $description = htmlentities($position['description']);
                    echo("<li>".$year.": ".$description."</li>\n");
                }
                echo ("</ul></p>\n");
            } // end if

            if (count($schools) > 0) {
                echo("<p>Education\n");
                echo("<ul>\n");
                foreach ($schools as $school) {
                    $year = htmlentities($school['year']);
                    $schoolName = htmlentities($school['name']);
                    echo("<li>".$year.": ".$schoolName."</li>\n");
                }
                echo ("</ul></p>\n");
            } // end if

        } // end foreach
        
    } // end if
    
}  // end if
?>
<a href='index.php'>Done</a>
</div>
</body>
</html>