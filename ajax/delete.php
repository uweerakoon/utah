<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

if (isset($_POST['table']) & isset($_POST['key']) & isset($_POST['id'])) {

    // Extract $_POST parameters.
    $table = $_POST['table'];
    $key = $_POST['key'];
    $id = $_POST['id'];
    
    // Get the data row for recording.
    $result = delete_sql($table, $key, $id);

    echo $result['message'];
    exit;
} else {
    echo status_message("Delete was not submitted properly.", "error");
    exit;
}
