<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define an Accomplishment class object (initializes class functions).
$accomplishment = new \Manager\Accomplishment($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "form-basic":
        // Append/display a page from the basic form.
        if (isset($_POST['accomplishment_id'])) {
            // Editing an accomplishment.
            echo $accomplishment->form($_POST['page'], null, $_POST['accomplishment_id']);
        } else {
            // Changing the page for a new accomplishment.
            echo $accomplishment->form($_POST['page'], $_POST['burn_id']);
        }
        break;
    case "burn-project-selector":
        echo $accomplishment->BurnProjectSelector();
        break;
    case "burn-selector":
        echo $accomplishment->burnSelector($_POST['burn_project_id']);
        break;
    case "toolbar":
        // Display the main form toolbar.
        echo $accomplishment->toolbar($_POST['page'], $_POST['burn_id'], $_POST['accomplishment_id']);
        break;
    case "form-complete":
        echo $accomplishment->completeForm($_POST['accomplishment_id']);
        break;
    case "complete":
        // Delete the daily burn with specified id.
        $result = $accomplishment->complete($_POST['accomplishment_id']);
        echo $result['message'];
        break;
    case "save":
        $result = $accomplishment->save(blah_decode($_POST['form']));
        echo $result['message'];
        break;
    case "owner-change-form":
        // Delete the burn plan (checks if the burn is an appropriate status).
        echo $accomplishment->ownerChangeForm($_POST['accomplishment_id']);
        break;
    case "owner-change":
        // Delete the burn plan (checks if the burn an appropriate status).
        $result = $accomplishment->ownerChange($_POST['accomplishment_id'], $_POST['user_id'], $_POST['district_id']);
        echo $result['message'];
        break;
    case "delete-confirmation":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo $accomplishment->deleteConfirmation($_POST['accomplishment_id']);
        break;
    case "delete":
        // Delete the daily burn with specified id.
        $result = $accomplishment->delete($_POST['accomplishment_id']);
        echo $result['message'];
        break;
    case "form-submit":
        // Display the submit to Utah.gov button form.
        echo $accomplishment->submittalForm($_POST['accomplishment_id']);
        break;
    case "review-detail":
        echo $accomplishment->reviewDetail($_POST['review_id']);
        break;
    case "submit":
        // Submit the burn to Utah.gov (change status to pending).
        $result = $accomplishment->submitUtah($_POST['accomplishment_id']);
        echo $result['message'];
        break;
    case "get-status":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo json_encode($accomplishment->getStatus($_POST['accomplishment_id']));
        break;
    case "edit-confirmation":
        // Delete the burn plan (checks if the burn an appropriate status).
        echo $accomplishment->editConfirmation();
        break;
    case "check-complete":
        // Check if the burn form contains all required data.
        $result = $accomplishment->validateRequired($_POST['accomplishment_id']);
        echo $result['message'];
        break;
    case "update":
        // Update burn plan info (checks if the burn plan is an appropriate status).
        $args = blah_decode($_POST['args']);
        $result = $accomplishment->update($args, $_POST['accomplishment_id']);
        echo $result['message'];
        break;
    default:
        echo status_message("Burn Accomplishment action does not exist.", "error");
        break;
}
