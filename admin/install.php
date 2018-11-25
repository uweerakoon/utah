<?php
include '../checklogin.php';
echo checklogin(array('title'=>'Install/Utah.gov','public'=>true,'suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

// This page creates the initial admin user. It will only work if there are no users.
$pdo = $db->get_connection();

if (isset($_POST['my'])) {
    $user_array = $_POST['my'];
    $new_user = new \Info\User($db, $user_array);

    $result = $new_user->saveUser();

    $sub_title = "Installation processed.";
    $disclaimer = "";
    $context = "Please delete this page from your server (admin/install.php).";
} else {
    $query = $pdo->query("SELECT user_id FROM users;");

    if ($query->rowCount() <= 0) {
        $sub_title = "Create an initial application administrator.";
        $temp_user = new \Info\User($db);
        $instructions = "This page has detected that there are no application users. To begin using the application, an administrator must be created.";
        $context = "Please ensure a quality password is chosen for this and all future admin level users.";
        $disclaimer = "This page is publicly accessible, please delete after use.";
        $html = $temp_user->userForm($temp_user, $new = true, $admin_use = false, $install = true);
    } else {
        $sub_title = "Installation already completed.";
        $disclaimer = "Please delete this page from your server (admin/install.php).";
        $context = "An administrator user already exists.";
    }
}



$title = "Utah Burn Manager Installation";

?>
<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <h1><?php echo $title; ?> <small><?php echo $sub_title; ?> </small></h1>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <p class="lead"><?php echo $instructions; ?> <span class="text-danger"><?php echo $disclaimer; ?></span></p>
            <p class="aq-note bg-danger"><?php echo $context; ?></p>
            <br>
            <?php
            echo $result['message'];
            echo $html; 
            ?>
        </div>
    </div>
</div>
