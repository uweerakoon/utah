<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Burn_Plan class object (initializes class functions).
$notify = new \Info\Notify($db);

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "detail":
        echo $notify->getDetail($_POST['notify_id']);
        break;
    case "read":
        $result = $notify->read($_POST['notify_id']);
        echo $result['message'];
        break;
    case "get-list":
        echo $notify->navList($_SESSION['user']['id']);
        break;
    case "get-count":
        echo $notify->getUserUnreadCount($_SESSION['user']['id']);
        break;
    default:
        echo status_message("Notification action does not exist.", "error");
        break;
}
