<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

if ($_POST['form'] == true) {
    // Form is true, so lets return the form html;
    $table = $_POST['table'];
    $key = $_POST['key'];
    $refresh_function = $_POST['refresh_function'];

    echo insert_form($table, $key, $refresh_function);
    
} else if ($_POST['submit'] == true) {
    // Pull the data.
    $table = $_POST['table'];
    $key = $_POST['key'];
    $args = blah_decode($_POST['args']);

    // Run a special case for the users table (to handle passwords).
    if ($table == 'users') {
        // Users special case.
        $new_user = new \Info\User($db, $args);
        $result = $new_user->saveUser();

        echo $result['message'];
    } else {
        // This is a generic insert.
        $result = insert_sql($table, $key, $args);
        
        echo $result['message'];
    }

} else {
    echo "Insert error";
}
