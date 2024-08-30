<?php


$server = "localhost";
$user = "demo";
$pass = "abc123";
$db = "api_booth";

$conn = mysqli_connect($server, $user, $pass, $db);
if (mysqli_connect_errno()) {
    printf("", mysqli_connect_error());
    exit(1);
}else{
    // printf("<p type='hidden'>Hi HELLO<br/><br/></p>");
}


?>