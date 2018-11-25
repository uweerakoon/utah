<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

$public_page = new \PublicZone\publicMap($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "filter-form":
        echo $public_page->filterForm();
        break;
    case "get-burns":
    	$args = form_json_decode($_POST['args']);
        echo $public_page->getMarkers($args);
        break;
    default:
        // Return error for unspecified case.
        echo modal_message("Public map action does not exist.", "error");
        break;
} 	