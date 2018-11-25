<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// now if we have a file_id we can move on
if (!empty($_GET['id'])) {
    $file_id = $_GET['id'];
    $sub_path = fetch_one("SELECT path FROM files WHERE file_id = $file_id;");

    $file_path = "/var/uploads/".$sub_path;

    // make sure the file is still there
    if (!file_exists($file_path) || !is_file($file_path)) {
        header('HTTP/1.0 404 Not Found');
        die("The file does not exist<br>file path: $file_path<br>File id:$file_id");
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='. urlencode(basename($file_path)));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    ob_clean();
    flush();
    readfile($file_path);
    exit;
} else {
    header('HTTP/1.0 404 Not Found');
    die('No file id was provided');
}
