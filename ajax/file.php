<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// Define a Burn_Plan class object (initializes class functions).
$pdo = $db->get_connection();

// Determine the task, based on the $_POST['action']:
switch($_POST['action']) {
    case "delete-confirmation":
        $file_id = $_POST['id'];
        $file = fetch_row("SELECT * FROM files WHERE file_id = $file_id");
        $file_name = end(explode('/', $file['path']));

        $html = "<div>
                <p class=\"text-center\">Are you sure you want to delete the file?</p>
                <button class=\"btn btn-danger btn-block\" onclick=\"File.deleteRecord($file_id)\">Delete <strong>".$file_name."</strong></button>
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";

        echo $html;
        break;
    case "delete":
        $file_id = $_POST['id'];
        $sub_path = fetch_one("SELECT path FROM files WHERE file_id = $file_id;");
        
        $file_path = "/var/uploads/".$sub_path;
        
        // make sure the file is still there
        if (!file_exists($file_path) || !is_file($file_path)) {
            $message = modal_message("The file could not be found, but has been removed from the database.", "error");
        } else {
            unlink($file_path);
        }

        $delete1 = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
        $delete1->execute(array($file_id));
        if ($delete1->rowCount() > 0) {
            $message = modal_message("The file has been deleted", "success");
        }

        $delete2 = $pdo->prepare("DELETE FROM burn_project_files WHERE file_id = ?");
        $delete2->execute(array($file_id));

        $delete3 = $pdo->prepare("DELETE FROM pre_burn_files WHERE file_id = ?");
        $delete3->execute(array($file_id));

        $delete3 = $pdo->prepare("DELETE FROM burn_files WHERE file_id = ?");
        $delete3->execute(array($file_id));

        $delete4 = $pdo->prepare("DELETE FROM accomplishment_files WHERE file_id = ?");
        $delete4->execute(array($file_id));

        $delete5 = $pdo->prepare("DELETE FROM documentation_files WHERE file_id = ?");
        $delete5->execute(array($file_id));

        $html = "<div>
                   $message
                <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button>
            </div>";

        echo $html;

        break;
    case "info":
        $file_id = $_POST['id'];
        $file = fetch_row("SELECT * FROM files WHERE file_id = $file_id;");
        $file_name = end(explode('/', $file['path']));

        if ($file['size_kb'] > 1000) {
            $file_size = round($file['size_kb']/1000, 2)." MB";
        } else {
            $file_size = round($file['size_kb'], 2)." kB";
        }

        $html = "<div>
            <p><strong>Name:</strong> ".$file_name."</p>
            <p><strong>Size:</strong> ".$file_size."</p>
            <p><strong>Comment:</strong> ".$file['comment']."</p>
            <div class=\"text-right\">
                <span class=\"label label-minimum\">".$file['added_on']."</span>
            </div>
            <div class=\"btn-group btn-group-justified\" style=\"padding-top: 8px;\">
                <div class=\"btn-group\">
                    <a class=\"btn btn-default\" href=\"/ajax/download.php?id=$file_id\">Download</a>
                </div>
                <div class=\"btn-group\">
                    <button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>
                </div>
            </div>
        </div>";

        echo $html;
        break;
    default:
        echo status_message("User reset action does not exist.", "error");
        break;
}
