<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a User class object
$ajax_user = new \Info\User($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
  case "form-profile":
    // Append / display a page from the basic form.
    if (isset($_POST['user_id'])) {
        echo $ajax_user->profileForm($_POST['user_id']);
    }
    break;
  case "form-admin":
    // Append / display a page from the basic form.
    echo $ajax_user->adminForm($_POST['user_id']);
    break;
  case "form-toolbar":
    // Display the daily burn form toolbar.
    echo $ajax_user->toolbar($_POST['user_id']);
    break;
  case "save-new":
    $args = blah_decode($_POST['args']);
    $new_user = new \Info\User($db, $args);
    echo json_encode($new_user->saveUser());
    break;
  case "save":
    error_log("UserAjax: error invalid action 'save'.", 0);
  //   // Save burn plan.
  //   $args = blah_decode($_POST['args']);
  //   $result = $ajax_user->save($args);
  //   echo $result['message'];
     break;
  case "update":
    // Update burn plan info (checks if the burn plan is an appropriate status).
    $args = blah_decode($_POST['args']);
    $result = $ajax_user->update($args, $_POST['user_id']);
    echo $result['message'];
    break;
  case "update-profile":
    // Update burn plan info (checks if the burn plan is an appropriate status).
    $args = blah_decode($_POST['args']);
    $result = $ajax_user->profileUpdate($args, $_POST['user_id']);
    echo $result['message'];
    break;
  case "update-admin":
    // Update burn plan info (checks if the burn plan is an appropriate status).
    $args = blah_decode($_POST['args']);
    $result = $ajax_user->adminUpdate($args, $_POST['user_id']);
    echo json_encode($result);
    break;
  case "delete-warning":
    $result = $ajax_user->warning($_POST['user_id']);
    echo json_encode($result);
    break;
  case "delete":
    $result = $ajax_user->delete($_POST['user_id']);
    echo json_encode($result);
    break;
  default:
    // Return error for unspecified case.
    echo modal_message("User action does not exist.", "error");
    break;
}
