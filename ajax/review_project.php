<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Burn_Plan class object (initializes class functions).
$reviewer = new \Manager\BurnProjectReview($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "form-review":
        echo $reviewer->reviewForm($_POST['burn_project_id']);
        break;
    case "form-edit-review":
        echo $reviewer->editReviewForm($_POST['burn_project_review_id']);
        break;
    case "save-review":
        $args = blah_decode($_POST['args']);
        $result = $reviewer->reviewSave($args);
        echo $result['message'];
        break;
    case "update-review":
        $args = blah_decode($_POST['args']);
        $result = $reviewer->reviewUpdate($args, $_POST['burn_project_review_id']);
        echo $result['message'];
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
