<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

if (!empty($_POST['args'])) {
    // and the json arguments
    $args = $_POST['args'];
    if (count($args)==1) {
        $tbl = show($args[0]);
    } else {
        $tbl = show($args);
    }
    // need to remove the first div at the begining and end
    // find the first `>'
    echo $tbl['html'];
    exit;
} else {
    echo status_message("Table not refreshed, no id supplied.", "error");
    exit;
}
