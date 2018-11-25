<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Daily_Burn class object (initializes class functions).
$temp_pre_burn = new \Manager\PreBurn($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "burn-plan-selector":
        echo $temp_pre_burn->burnSelector($_SESSION['user']['id']);
        break;
    case "form-basic":
        // Append / display a page from the basic form.
        if (isset($_POST['burn_project_id'])) {
            echo $temp_pre_burn->form($_POST['page'], $_POST['pre_burn_id'], $_POST['burn_project_id']);
        } else {
            echo $temp_pre_burn->form($_POST['page'], $_POST['pre_burn_id']);
        }
        break;
    case "form-toolbar":
        // Display the daily burn form toolbar.
        echo $temp_pre_burn->toolbar($_POST['page'], $_POST['pre_burn_id']);
        break;
    case "form-receptor":
        // Display the new receptor modal form.
        echo $temp_pre_burn->receptorForm($_POST['origin'], $_POST['burn_project_id']);
        break;
    case "save":
        // Save burn plan.
        $args = blah_decode($_POST['args']);
        $result = $temp_pre_burn->save($args);
        echo $result['message'];
        break;
    case "update":
        // Update burn plan info (checks if the burn plan is an appropriate status).
        $args = blah_decode($_POST['args']);
        $result = $temp_pre_burn->update($args, $_POST['pre_burn_id']);
        echo $result['message'];
        break;
    case "submit":
        // Submit the burn to Utah.gov (change status to pending).
        $result = $temp_pre_burn->submitUtah($_POST['pre_burn_id']);
        echo $result['message'];
        break;
    case "get-status":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo json_encode($temp_pre_burn->getStatus($_POST['pre_burn_id']));
        break;
    case "edit-warning":
        // Edit the daily burn (checks if the burn is an appropriate status).
        echo $temp_pre_burn->noEditWarning($_POST['pre_burn_id']);
        break;
    case "owner-change-form":
        // Delete the burn plan (checks if the burn is an appropriate status).
        echo $temp_pre_burn->ownerChangeForm($_POST['pre_burn_id']);
        break;
    case "owner-change":
        // Delete the burn plan (checks if the burn an appropriate status).
        $result = $temp_pre_burn->ownerChange($_POST['pre_burn_id'], $_POST['user_id'], $_POST['district_id']);
        echo $result['message'];
        break;
    case "delete-confirmation":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo $temp_pre_burn->deleteConfirmation($_POST['pre_burn_id']);
        break;
    case "delete":
        // Delete the daily burn with specified id.
        $result = $temp_pre_burn->delete($_POST['pre_burn_id']);
        echo $result['message'];
        break;
    case "form-submit":
        // Display the submit to Utah.gov button form.
        echo $temp_pre_burn->submittalForm($_POST['pre_burn_id']);
        break;
    case "review-detail":
        echo $temp_pre_burn->reviewDetail($_POST['review_id']);
        break;
    case "check-complete":
        // Check if the burn form contains all required data.
        $result = $temp_pre_burn->validateRequired($_POST['pre_burn_id']);
        echo $result['message'];
        break;
    case "condition-detail":
        echo $temp_pre_burn->conditionDetail($_POST['condition_id']);
        break;
    case "form-revision":
        // Display the revision & renewal form.
        echo $temp_pre_burn->revisionForm($_POST['pre_burn_id']);
        break;
    case "renew":
        // Renew the pre-burn.
        echo $temp_pre_burn->renew($_POST['pre_burn_id']);
        break;
    case "revise":
        // revise the pre-burn.
        echo json_encode($temp_pre_burn->revise($_POST['pre_burn_id'], $_POST['type']), true);
        break;
    default:
        // Return error for unspecified case.
        echo modal_message("Pre-burn request action does not exist.", "error");
        break;
}
