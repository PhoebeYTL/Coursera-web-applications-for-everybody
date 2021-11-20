<?php

//include_once("../config.php");
//require_once "bootstrap.php";

//include_once("bootstrap.php");

if (!isset($_GET['term'])) {
    die('Mising required parameter');
}

if (!isset($_COOKIE[session_name()])) {
    die("Must be logged in");
}

session_start();

if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");
}

require_once 'pdo.php';
header('Content-Type: application/json; charset=utf-8');

$term = $_GET['term'];
error_log("looking up typeahead term=".$term);

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array(':prefix' => $term."%"));

$retval = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $retval[] = $row['name'];
}

echo(json_encode($retval, JSON_PRETTY_PRINT));