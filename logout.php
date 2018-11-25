<?php
session_start();
session_unset();
session_destroy();

// Get the server's protocol and host info.
$protocol = "http";
$server = $_SERVER['HTTP_HOST'];
if (isset($_SERVER['https']) && $_SERVER['HTTPS'] != 'off') {
    $protocol = "https";
}

header("location: $protocol://$server/index.php");
