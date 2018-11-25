<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

$tmp_user = new \Info\User($db);

if ($_POST['form'] == true) {
    // Form is true, so lets return the form html;
    $refresh_function = $_POST['refresh_function'];



    echo $tmp_user->groupForm();
} elseif ($_POST['submit'] == true) {
    //$args = json_decode($_POST['args'], true);
    $args = blah_decode($_POST['args']);
    $users = $args['users'];
    $districts = $args['districts'];

    foreach ($users as $user) {
        foreach ($districts as $district) {
            $result = $tmp_user->saveUserGroup($user, $district);
        }
    }

    echo $result['message'];
}
