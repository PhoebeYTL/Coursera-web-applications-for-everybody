<!--
    add.php add a new Profile entry. Make sure to mark the entry with the foreign key user_id of the currently logged in user. (create)
-->

<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if (!isset($_SESSION['user_id'])) {
    die('ACCESS DENIED');
}

// when user press cancel button
if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

// Handle the incoming POST data
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

    /*validation*/
    $msg = validateProfile();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
    }

    $msg = validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
    }

    // TODO: Validate education
    $msg = validateEdu();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
    }
    
    /*Begin to add the data*/
    // Database: Profile
    $stmt = $pdo->prepare('INSERT INTO Profile
        (user_id, first_name, last_name, email, headline, summary)
        VALUES (:uid, :fn, :ln, :em, :he, :su)');
      
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );

    $profile_id = $pdo->lastInsertId();

    // Database: Position - non mandatory for all field
    $rank = 1;
    for ($i=1; $i<=9; $i++) {
        if (!isset($_POST['year'.$i])) {
            continue;
        }

        if (!isset($_POST['desc'.$i])) {
            continue;
        }

        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, rank, year, description)
            VALUES (:pid, :rank, :year, :desc)');
        
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }
   
    // Database: Education
    insertEducations($pdo, $profile_id);

    $_SESSION['success'] = "Profile added";
    header("Location: index.php");
    return; 
}

?>


<!DOCTYPE html>
<html>

<head>
    <title>Yuen Ting Lai</title>
    <?php require_once "bootstrap.php"; ?>

    <script>
    /*Add and remove position fields*/
    countPos = 0; // global variable to keep track of how many time the click happen
    countEdu = 0;

    $(document).ready(function() {

        $('#addPos').click(function(event) {

            event.preventDefault();
            if (countPos >= 9) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            //window.console && console.log("Adding position "+countPos);
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
    
            $('#edu_fields').append(
                '<div id="edu'+countEdu+'"> \
                <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
                <input type="button" value="-" \
                onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br> \
                <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
                </p></div>'

            );
            
            
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

<h1>Adding Profile for
<?php
if (isset($_SESSION['name'])) {
    echo htmlentities($_SESSION['name']);
}
?>
</h1>

<?php
    if (isset($_SESSION["error"]) ) {
        echo('<p style="color:red">'.$_SESSION["error"]."</p>\n");
        unset($_SESSION["error"]);
    }
?>

<form method="post">
    <p>First Name:
    <input type="text" name="first_name" size="60"/></p>

    <p>Last Name:
    <input type="text" name="last_name" size="60"/></p>
    
    <p>Email:
    <input type="text" name="email"/></p>
    
    <p>Headline:<br>
    <input type="text" name="headline"/></p>

    <p>Summary:<br>
    <input type="text" name="summary"/></p>
    
    <p>Position:<br> 
    <input type="submit" id="addPos" value="+"></p>   
    <div id="position_fields">
    </div>

    <p>Education:<br> 
    <input type="submit" id="addEdu" value="+"></p>
    <div id="edu_fields">
    </div>

    <input type="submit" value="Add">
    <input type="submit" name="cancel" value="Cancel">
</form>

</div>
</body>
</html>
