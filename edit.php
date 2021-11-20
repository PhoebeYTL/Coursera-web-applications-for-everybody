<?php
session_start();
require_once "pdo.php";
require_once "util.php";

// If the user is not log in, access is deny
if (!isset($_SESSION['user_id'])) {
    die('ACCESS DENIED');
}

// If the user press cancel, they go back to index.php
if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

// Make sure that profile_id is present (GET that was passed in to the initial URL)
if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

// Validate profile id
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :profile_id AND user_id = :user_id");
$stmt->execute(array(
    ":profile_id" => $_GET['profile_id'],
    ":user_id" => $_SESSION['user_id'])
);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header('Location: index.php') ;
    return;
} 
  

// Validate POST data, update the actual SQL table if passes validation
// POST data will be empty on initial run
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['profile_id'])) {

    $msg = validateProfile();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
    }

    $msg = validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
    }

    $msg = validateEdu();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
    }

    /*Begin to update the data*/
    // Database: Profile
    $sql = "UPDATE `profile` 
            SET last_name = :ln, first_name = :fn, email = :em, headline = :hl, summary = :sm
            WHERE profile_id = :pid AND user_id= :uid";
    echo($sql);
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':ln' => $_POST['last_name'],
        ':fn' => $_POST['first_name'],
        ':em' => $_POST['email'],
        ':hl' => $_POST['headline'],
        ':sm' => $_POST['summary'],
        ':uid' => $_SESSION['user_id'],
        ':pid' => $_POST['profile_id']));
    
    // Database: Position
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid'); // Delete everything
    $stmt->execute(array(":pid" => $_REQUEST['profile_id']));
    insertPositions($pdo, $_REQUEST['profile_id']); // Re-insert everything 

    // Database: Education
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid'); // Delete everything
    $stmt->execute(array(":pid" => $_REQUEST['profile_id']));    
    insertEducations($pdo, $_REQUEST['profile_id']); // Re-insert everything

    
    $_SESSION['success'] = 'Profile edited';
    header('Location: index.php') ;
    return;
}

// Initial data pull out from the database
$firstname = htmlentities($row['first_name']);
$lastname = htmlentities($row['last_name']);
$email = htmlentities($row['email']);
$headline = htmlentities($row['headline']);
$summary = htmlentities($row['summary']);
$profile_id = $row['profile_id'];
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Yuen Ting Lai</title>
    <?php require_once "bootstrap.php"; ?>

    <script>
    /*Add and remove position and education fields*/
    countPos = <?= count($positions); ?>;
    countEdu = <?= count($schools); ?>;

    $(document).ready(function() {

        //window.console && console.log("check document ready");
        $('#addPos').click(function(event) {

            event.preventDefault();
            if (countPos >= 9) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;

            $('#position_fields').append(
                '<div id="position'+countPos+'"> \
                <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
                <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
                <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
                </div>');
        });

        $('#addEdu').click(function(event) {
            
            event.preventDefault();
            if (countEdu >= 9) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countEdu++;

            // Grab some HTML with hot spots and insert into the DOM     
            var source = $("#edu-template").html();
            $("#edu_fields").append(source.replace(/@COUNT@/g, countEdu));
            // Same code as the two lines above
            /*
            $('#edu_fields').append(
                '<div id="edu'+countEdu+'"> \
                <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
                <input type="button" value="-" \
                onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br> \
                <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
                </p></div>'

            );
            */
            
            // Add the even handler to the new ones
            $('.school').autocomplete({
                source: "school.php"
            });

        });
    
    });

</script>

</head>

<body>
<div class="container">

<h1>Editing Profile for 
<?php
echo($firstname);
?>
</h1>

<?php
flashMessages();
?>

<form method="post">
    <p>First Name:
    <input type="text" name="first_name" value="<?= $firstname ?>"/></p>

    <p>Last Name:
    <input type="text" name="last_name" value="<?= $lastname ?>"/></p>
    
    <p>Email:
    <input type="text" name="email" value="<?= $email ?>"/></p>
    
    <p>Headline:
    <input type="text" name="headline" value="<?= $headline ?>"/></p>

    <p>Summary:
    <input type="text" name="summary" value="<?= $summary ?>"/></p>

    <input type="hidden" name="profile_id" value="<?= $profile_id ?>">

    <?php
        $pos = 0;
        echo('<p>Position:<input type="submit" id="addPos" value="+">'."\n");
        echo('<div id="position_fields">'."\n");
        if (count($positions) > 0) {
            foreach($positions as $position) {
                $pos++;
                echo('<div id="position'.$pos.'">'."\n");
                echo('<p>Year: <input type="text" name="year'.$pos.'" value="'.htmlentities($position['year']).'" />'."\n");
                echo('<input type="button" value="-" onclick="$(\'#position'.$pos.'\').remove();return false;">'."\n");
                echo("</p>\n");
                echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
                echo(htmlentities($position['description'])."\n\n");
                echo("</textarea>\n");
                echo("</div>\n");
            }
        }
        echo("</div></p>\n");

        $countEdu = 0;
        echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
        echo('<div id="edu_fields">'."\n");
        if (count($schools) > 0) {
            foreach ($schools as $school) {
                $countEdu++;
                echo('<div id="edu'.$countEdu.'">');
                echo("<p>\n");
                echo('Year: <input type="text" name="edu_year'.$countEdu.'" value="'.htmlentities($school['year']).'" />'."\n");
                echo('<input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;">'."\n");
                echo("</p>\n");
                echo("<p>\n");
                echo('School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school" value="'.htmlentities($school['name']).'" />');      
                echo("</p>\n");
                echo("</div>\n");
            }
        }
        echo("</div></p>\n");

    ?>


    <input type="submit" value="Save">
    <input type="submit" name="cancel" value="Cancel">
</form>



<!-- HTML with substitution hot spots, string replace-->
<script id="edu-template" type="text">
    <div id="edu@COUNT@">
        <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
        <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
        <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
        </p>
    </div>
</script>
</div>
</body>
</html>
