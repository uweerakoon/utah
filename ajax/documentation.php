<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Daily_Burn class object (initializes class functions).
$temp_burn = new \Manager\BurnDocumentation($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "burn-project-selector":
        echo $temp_burn->burnProjectSelector($_SESSION['user']['id']);
        break;
    case "accomplishment-selector":
        echo $temp_burn->accomplishmentSelector($_SESSION['user']['id'], $_POST['burn_project_id']);
        break;
    case "form-basic":
        // Append / display a page from the basic form.
        if (isset($_POST['accomplishment_id'])) {
            echo $temp_burn->form($_POST['page'], $_POST['documentation_id'], $_POST['accomplishment_id']);
        } else {
            echo $temp_burn->form($_POST['page'], $_POST['documentation_id']);
        }
        break;
    case "form-toolbar":
        // Display the daily burn form toolbar.
        echo $temp_burn->toolbar($_POST['page'], $_POST['documentation_id']);
        break;
    case "form-observation":
        // Display the new receptor modal form.
        echo $temp_burn->observationForm();
        break;
    case "save":
        // Save burn plan.
        $args = blah_decode($_POST['args']);
        $result = $temp_burn->save($args);
        echo $result['message'];
        break;
    case "update":
        // Update burn plan info (checks if the burn plan is an appropriate status).
        $args = blah_decode($_POST['args']);
        $result = $temp_burn->update($args, $_POST['documentation_id']);
        echo $result['message'];
        break;
    case "submit":
        // Submit the burn to Utah.gov (change status to pending).
        $result = $temp_burn->submitUtah($_POST['documentation_id']);
        echo $result['message'];
        break;
    case "get-status":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo json_encode($temp_burn->getStatus($_POST['documentation_id']));
        break;
    case "edit-warning":
        // Edit the daily burn (checks if the burn is an appropriate status).
        echo $temp_burn->noEditWarning();
        break;
    case "owner-change-form":
        // Delete the burn plan (checks if the burn is an appropriate status).
        echo $temp_burn->ownerChangeForm($_POST['documentation_id']);
        break;
    case "owner-change":
        // Delete the burn plan (checks if the burn an appropriate status).
        $result = $temp_burn->ownerChange($_POST['documentation_id'], $_POST['user_id'], $_POST['district_id']);
        echo $result['message'];
        break;
    case "delete-confirmation":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo $temp_burn->deleteConfirmation($_POST['documentation_id']);
        break;
    case "delete":
        // Delete the daily burn with specified id.
        $result = $temp_burn->delete($_POST['documentation_id']);
        echo $result['message'];
        break;
    case "form-submit":
        // Display the submit to Utah.gov button form.
        echo $temp_burn->submittalForm($_POST['documentation_id']);
        break;
    case "review-detail":
        echo $temp_burn->reviewDetail($_POST['review_id']);
        break;
    case "check-complete":
        // Check if the burn form contains all required data.
        $result = $temp_burn->validateRequired($_POST['documentation_id']);
        echo $result['message'];
        break;
    case "condition-detail":
        echo $temp_burn->conditionDetail($_POST['condition_id']);
        break;
    default:
        // Return error for unspecified case.
        echo modal_message("Burn documentation action does not exist.", "error");
        break;
}
