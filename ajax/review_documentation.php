<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Burn_Plan class object (initializes class functions).
$reviewer = new \Manager\BurnDocumentationReview($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "form-review":
        echo $reviewer->reviewForm($_POST['documentation_id']);
        break;
    case "form-edit-review":
        echo $reviewer->editReviewForm($_POST['documentation_review_id']);
        break;
    case "save-review":
        $args = blah_decode($_POST['args']);
        $result = $reviewer->reviewSave($args);
        echo $result['message'];
        break;
    case "update-review":
        $args = blah_decode($_POST['args']);
        $result = $reviewer->reviewUpdate($args, $_POST['documentation_review_id']);
        echo $result['message'];
        break;
    case "form-approve":
        echo $reviewer->approveForm($_POST['documentation_id']);
        break;
    case "approve":
        $result = $reviewer->approveBurn($_POST['documentation_id']);
        echo $result['message'];
        break;
    case "form-pre-approve":
        echo $reviewer->preApproveForm($_POST['documentation_id']);
        break;
    case "pre-approve":
        $result = $reviewer->preApproveBurn($_POST['documentation_id']);
        echo $result['message'];
        break;
    case "form-disapprove":
        echo $reviewer->disapproveForm($_POST['documentation_id']);
        break;
    case "disapprove":
        $result = $reviewer->disapproveBurn($_POST['documentation_id']);
        echo $result['message'];
        break;
    case "form-condition":
        echo $reviewer->conditionForm($_POST['documentation_id']);
        break;
    case "edit-condition":
        echo $reviewer->conditionEdit($_POST['burn_condition_id']);
        break;
    case "save-condition":
        $args = blah_decode($_POST['args']);
        $result = $reviewer->conditionSave($args);
        echo $result['message'];
        break;
    default:
        echo status_message("Burn plan review action does not exist.", "error");
        break;
}
