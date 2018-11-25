<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Burn_Plan class object (initializes class functions).
$pdo = $db->get_connection();

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case 'push':
        $key = $_POST['key'];
        $page = $_POST['page'];
        $active = json_decode($_POST['active']);
        $_SESSION['user']['filter_state'][$page][$key] = $active;
        break;
    case 'push-date':
        $key = $_POST['key'];
        $page = $_POST['page'];
        $date = $_POST['date'];
        $_SESSION['user']['filter_state'][$page][$key] = $date;
        break;
    case 'push-sort':
        $key = $_POST['key'];
        $page = $_POST['page'];
        $column = $_POST['column'];
        $direction = $_POST['direction'];
        $_SESSION['user']['filter_state'][$page][$key] = array('column'=>$column,'direction'=>$direction);
        break;
    case 'get':
        $key = $_POST['key'];
        $page = $_POST['page'];
        $active = $_SESSION['user']['filter_state'][$page][$key];
        $json = json_encode($active);
        echo $json;
        break;
    default:
        echo "dtFilter.filter_state action not found";
        break;
}
