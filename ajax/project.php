<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Burn_Plan class object (initializes class functions).
$temp_project = new \Manager\BurnProject($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "form-basic":
        if (isset($_POST['district_id'])) {
            echo $temp_project->form($_POST['page'], $_POST['district_id']);
        } elseif (isset($_POST['burn_project_id'])) {
            echo $temp_project->form($_POST['page'], null, $_POST['burn_project_id']);
        } else {
            echo $temp_project->form($_POST['page']);
        }
        break;
    case "district-selector":
        // When a new burn form is requested, but the district id is not specified.
        echo $temp_project->districtForm();
        break; 
    case "form-toolbar":
        // Display the main form toolbar.
        echo $temp_project->toolbar($_POST['page'], $_POST['burn_project_id']);
        break;
    case "form-submit":
        // Display the submit to Utah.gov button form.
        echo $temp_project->submittalForm($_POST['burn_project_id']);
        break;
    case "save":
        // Save burn plan.
        $args = blah_decode($_POST['args']);
        echo $temp_project->saveBurn($args);
        break;
    case "update":
        // Update burn plan info (checks if the burn plan is an appropriate status).
        $args = blah_decode($_POST['args']);
        $result = $temp_project->updateBurn($args, $_POST['burn_project_id']);
        echo $result['message'];
        break;
    case "get-status":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo json_encode($temp_project->getStatus($_POST['burn_project_id']));
        break;
    case "edit-confirmation":
        // Edit the burn plan (checks if the burn is an appropriate status).
        echo $temp_project->editConfirmation();
        break;
    case "edit-approved":
        // Confirmation html for editing a previously approved plan.
        echo $temp_project->editApproved($_POST['burn_project_id']);
        break;
    case "to-draft":
        // Convert an approved plan to draft, following user confirmation.
        echo $temp_project->toDraft($_POST['burn_project_id']);
        break;
    case "to-archive":
        // Convert an approved plan to draft, following user confirmation.
        echo $temp_project->toArchive($_POST['burn_project_id']);
        break;
    case "to-approved":
        // Convert an approved plan to draft, following user confirmation.
        echo $temp_project->toApproved($_POST['burn_project_id']);
        break;
    case "delete-confirmation":
        // Delete the burn plan (checks if the burn is an appropriate status).
        echo $temp_project->deleteConfirmation($_POST['burn_project_id']);
        break;
    case "delete":
        // Delete the burn plan (checks if the burn an appropriate status).
        $result = $temp_project->delete($_POST['burn_project_id']);
        echo $result['message'];
        break;
    case "owner-change-form":
        // Delete the burn plan (checks if the burn is an appropriate status).
        echo $temp_project->ownerChangeForm($_POST['burn_project_id']);
        break;
    case "owner-change":
        // Delete the burn plan (checks if the burn an appropriate status).
        $result = $temp_project->ownerChange($_POST['burn_project_id'], $_POST['user_id'], $_POST['district_id']);
        echo $result['message'];
        break;
    case "register-select":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo $temp_project->registerSelect();
        break;
    case "register-confirmation":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo $temp_project->registerConfirmation($_POST['burn_project_id']);
        break;
    case "register":
        // Delete the burn plan (checks if the burn an appropriate status).
        $result = $temp_project->register($_POST['burn_project_id']);
        echo $result['message'];
        break;
    case "submit":
        // Submit the burn to Utah.gov (change status to pending).
        $result = $temp_project->submitUtah($_POST['burn_project_id']);
        echo $result['message'];
        break;
    case "check-complete":
        // Check if the burn form contains all required data.
        $result = $temp_project->validateRequired($_POST['burn_project_id']);
        echo $result['message'];
        break;
    case "review-detail":
        echo $temp_project->reviewDetail($_POST['review_id']);
        break;
    default:
        // Return error for unspecified case.
        echo status_message("Burn plan action does not exist.", "error");
        break;
}
