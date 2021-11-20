<!--
    logout.php will log the user out by clearing data in the session and redirecting back to index.php. 
-->

<?php

session_start();
unset($_SESSION['name']);
unset($_SESSION['user_id']);
header('Location: index.php');

?>