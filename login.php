<!--
  login.php will present the user the login screen with an email address and password to get the user to log in. 
  If there is an error, redirect the user back to the login page with a message. 
  If the login is successful, redirect the user back to index.php after setting up the session. 
  In this assignment, you will need to store the user's hashed password in the users table as described below.  
-->

<?php // Do not put any HTML above this line
session_start();
require_once "pdo.php";
require_once "util.php";

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
//$stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123

// Check to see if we have some POST data, if we do process it
if (isset($_POST['email']) && isset($_POST['pass'])) {
    
    unset($_SESSION["name"]);  // Logout current user
    
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        
        $_SESSION["error"] = "User name and password are required";
        header('Location: login.php');
        return;
    
    } else if (strpos($_POST['email'],'@') == false) {
        
        $_SESSION["error"] = "Email must have an at-sign (@)";
        header('Location: login.php');
        return;
    
    } else {
        // pass data validation, check email and password
        $check = hash('md5', $salt.$_POST['pass']);
        $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
        $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row !== false) {
            error_log("Login success ".$_POST['email']);
            $_SESSION['name'] = $_POST['email'];
            $_SESSION['user_id'] = $row['user_id'];
            header("Location: index.php");
            return;
        } else {
            error_log("Login fail ".$_POST['email']." $check");
            $_SESSION["error"] = "Incorrect password";
            header('Location: login.php');
            return;
        }
    
    }
}

?>


<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>

<script>
/*Javascript validation t ensure all fields are filled out*/
function doValidate() {

    try {
        pw = document.getElementById('id_1723').value;
        email = document.getElementById('nam').value;
        console.log("Validating email = " + email + " and password = " + pw);
        if (pw == null || pw == "" || email == null || email == "") {
            alert("Both fields must be filled out");
            return false;
        }
        return true;

    } catch(e) {

        return false;

    }

    return false;

}
</script>

<title>Yuen Ting Lai - Login Page</title>
</head>
<body>
<div class="container">
<h1>Please Log In</h1>

<?php
flashMessages();
?>

<form method="POST">
    <label for="nam">Email</label>
    <input type="text" name="email" id="nam"><br/>
    <label for="id_1723">Password</label>
    <input type="text" name="pass" id="id_1723"><br/>
    <input type="submit" onclick="return doValidate();" value="Log In">
    <input type="submit" name="cancel" value="Cancel">
</form>

</div>
</body>
