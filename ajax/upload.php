<?php
include "../checklogin.php";
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

$user_id=$_SESSION['user']['id'];
$excluded = array('__MACOSX',
    '.DS_Store',
    'Thumbs.db');
$re_excluded = "/(" . implode("|", $excluded) . ")/";

// Establish a DB connection if its not already.
if (!isset($pdo)) {
    $pdo = $db->get_connection();
}

if (isset($_FILES['my']) && $_FILES['my']['error'][0] == UPLOAD_ERR_OK && isset($_POST['my'])) {

    // Defaults & Extract
    $result = array('result'=>false);
    $tmp_file = $_FILES['my']['tmp_name'][0];
    $file_name = $_FILES['my']['name'][0];
    $new_file_name = substr(uniqid(), 0, 6)."-".$file_name;
    $file_size = $_FILES['my']['size'][0];
    extract($_POST['my']);

    // Get the database's upload directory.
    $day_folder = date('Y_m_d');
    $dir = "/var/uploads/$day_folder/$user_id";
    $file_path = $dir."/".$file_name;
    $new_file_path = $dir."/".$new_file_name;

    // Directory and file validation.
    if (!file_exists($dir)) {
        // Directory doesnt exist. Try to make it:
        if (!mkdir($dir, 0777, true)) {
            // Directory creation failed.
            $result['returning']="The new directory ($dir) could not be created";
            echo status_message($result['returning'], "warning");
            exit;
        };
    }

    if (file_exists($new_file_path)) {
        // File already exists.
        $result['returning']="File ($new_file_path) already exists";
        echo status_message($result['returning'], "warning");
        exit;
    } else {
        if (move_uploaded_file($tmp_file, $new_file_path)) {
            $ext = pathinfo($new_file_path, PATHINFO_EXTENSION);
            $fpath = $new_file_path;
            $ctime = date("Y-m-d H:i:s", filectime($new_file_path));
            $fsize = $file_size/1000;
            $tags = "{".implode(",", $tags)."}";
            
            if ($ext=="zip") {
            
                // Process a .zip file
                umask(0);
                $zfiles = unzip($fpath, $dir);
        
                // register them all
                foreach ($zfiles as $fpath) {
                    if (preg_match($re_excluded, $fpath) > 0) {
                        // Excluded
                        //echo status_message($fpath." excluded","warning");
                    } else {
                    
                        $file_trunc = str_replace('/var/uploads/', '', $fpath);
                        $fsize = filesize($fpath)/1000;
                        $ctime = date("Y-m-d H:i:s", filectime($fpath));
            
                        // Add the file to the files table.
                        $files_sql = $pdo->prepare("INSERT INTO files (path, size_kb, comment, added_by, added_on) VALUES (?, ?, ?, ?, ?)");
                        $files_sql->execute(array($file_trunc, $fsize, $comment, $user_id, now()));
                        
                        $file_id_sql = $pdo->prepare("SELECT file_id FROM `files` WHERE `path` = ? AND `added_by` = ?;");
                        $file_id_sql->execute(array($file_trunc, $user_id));
                        if ($file_id_sql->rowCount() > 0) {
                            $file_id = $file_id_sql->fetchColumn();
                        }
    
                        // Add the file ref insert.
                        $ref_pkey = strtolower(format_table_name($ref_table)."_id");
                        $ref_table = strtolower(format_table_name($ref_table)."_files");
                        $ref_sql = $pdo->prepare("INSERT INTO `$ref_table` (`$ref_pkey`, `file_id`) VALUES (?, ?);");
                        $ref_sql->execute(array($ref_id, $file_id));
                    }
                }

                // The file is extracted, delete.
                unlink($fpath);
        
                // The zip is processed, exit
                echo status_message("The .zip file was uploaded successfully.", "success");
                exit;
                
            } else {
            
                if (file_exists($fpath)) {
                    $file_trunc = str_replace('/var/uploads/', '', $fpath);
                    
                    // Add the file to the files table.
                    $files_sql = $pdo->prepare("INSERT INTO `files` (`path`, `size_kb`, `comment`, `added_by`, `added_on`) VALUES (?, ?, ?, ?, ?)");
                    $files_sql->execute(array($file_trunc, $fsize, $comment, $user_id, now()));
                    
                    $file_id_sql = $pdo->prepare("SELECT file_id FROM `files` WHERE `path` = ? AND `added_by` = ?;");
                    $file_id_sql->execute(array($file_trunc, $user_id));
                    if ($file_id_sql->rowCount() > 0) {
                        $file_id = $file_id_sql->fetchColumn();
                    }

                    // Add the file ref insert.
                    $ref_pkey = strtolower(format_table_name($ref_table)."_id");
                    $ref_table = strtolower(format_table_name($ref_table)."_files");
                    $ref_sql = $pdo->prepare("INSERT INTO `$ref_table` (`$ref_pkey`, `file_id`) VALUES (?, ?);");
                    $ref_sql->execute(array($ref_id, $file_id));

                    $return['html'] = status_message("The file was uploaded successfully.","success");
                    $return['file'] = array('file_id'=>$file_id,'path'=>$path,'size_kb'=>$fsize);
                    //$return = json_encode($return, true);

                    echo $return['html'];
                    exit;
                    
                } else {
                    echo status_message("The file could not be moved from $tmp_file to $fpath.", "warning");
                }
            
            }
                    
        } else {
            echo status_message("File couldn't be written.", "warning");
        }
    }
    
} elseif (isset($_POST['refTable']) && isset($_POST['id'])) {

    // Construct the form
    $ctls=array(
        'ref_table'=>array('type'=>'hidden2','value'=>$_POST['refTable']),
        'ref_id'=>array('type'=>'hidden2','value'=>$_POST['id']),
        'file'=>array('type'=>'file','label'=>''),
        'comment'=>array('type'=>'memo','label'=>'Comment'),
    );

    // Print the form
    echo mkForm(array('controls'=>$ctls,'onclick'=>'Uploader.upload()','suppress_legend'=>true,'theme'=>'modal','cancel'=>true,'interface'=>false));
    
    echo "<div class=\"progress\" style=\"display:none;\">
      <div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:0%;\">
        0%
      </div>
    </div>
    <div id=\"upload_file_output\"></div>";
} else {
    echo modal_message("Uploading is not allowed from here.", "error")."<button class=\"btn btn-default\" onclick=\"cancel_modal()\">Close</button>";
}
