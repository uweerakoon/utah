<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "":
        echo $reviewer->reviewForm($_POST['burn_project_id']);
        break;
    case "save-review":
        echo "1";
        break;
    case "form-approve":
        echo $reviewer->approveForm($_POST['burn_project_id']);
        break;
    case "approve":
        $result = $reviewer->approveBurnProject($_POST['burn_project_id']);
        echo $result['message'];
        break;
    default:
        echo status_message("Burn plan review action does not exist.", "error");
        break;
}
