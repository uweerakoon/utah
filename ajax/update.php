<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

if ($_POST['form'] == true) {
    // Form is true, so lets return the form html;
    $table = $_POST['table'];
    $key = $_POST['key'];
    $id = $_POST['id'];
    $refresh_function = $_POST['refresh_function'];

    echo update_form($table, $key, $id, $refresh_function);
    
} else if ($_POST['submit'] == true) {
    // Pull the data.

    $table = $_POST['table'];
    $key = $_POST['key'];
    $id = $_POST['id'];
    $args = blah_decode($_POST['args']);

    $result = update_sql($table, $key, $id, $args);
    echo $result['message'];
} else {
    echo "Update error";
}
