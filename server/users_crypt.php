<?php

/**
 *	users.py helper script for generating the temporary password such that user can log in.
 */

$cost = 13; // This is the hash performance cost. Default = 10. Higher requires more resource, but provides better protection.
$password = bin2hex(mcrypt_create_iv(4, MCRYPT_DEV_URANDOM));

$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.'); // Generate random salt for this user.
$salt = sprintf("$2a$%02d$", $cost) . $salt; // Specify the salt algorithm ($2a$ = blowfish), includes cost.
$hash = crypt($password, $salt); // This is the backwards compatible (if php 5.5+ password_hash() condenses).

echo (json_encode(array('password'=>$password,'hash'=>$hash,'salt'=>$salt), true));

?>