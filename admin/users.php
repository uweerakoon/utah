<?php
$page_title = "User Manager/Utah.gov";
include '../checklogin.php';
$extra_js = "<script>User = new User();</script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js));

if (isset($_POST['my'])) {
    $user_array = $_POST['my'];
    $new_user = new User($db, $user_array);

    $result = $new_user->Save_User();
    $message = $result['message'];
}
?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <? echo $message; ?>
                <button class="pull-right btn btn-sm btn-default" onclick="User.adminForm()">New User</button>
                <h1>User Manager</h1>
                <hr>
            </div>
        </div>
        <div id="interface-error" style="display:none">
            <div class="row">
                <div class="col-sm-12">
                    <div id="error-message-block"></div>
                </div>
            </div>
        </div>
        <div id="interfaceForm" style="display:none">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-block">
                    </div>
                </div>
            </div>
        </div>
        <div id="interfaceMain">
            <div class="row">
                <div class="col-sm-12">
                    <?php
                        $sql = "SELECT u.user_id, l.user_level_name as \"Type\", full_name as \"Full Name\", email as \"Email\",
                        COALESCE(CONCAT(u.address, '<br>', u.address_b, '<br>', u.city, ', ', u.state, ' ', u.zip), u.address) as \"Address\",
                        u.phone as \"Phone\", a.abbreviation as \"Agency\", GROUP_CONCAT(COALESCE(CONCAT(d.identifier, ' - ', d.district), d.district) SEPARATOR ', ') as \"Sub\",
                        IF(u.active = 1, 'True', 'False') as \"Active\"
                        FROM users u
                        JOIN user_levels l ON (u.level_id = l.user_level_id)
                        LEFT JOIN agencies a ON (u.agency_id = a.agency_id)
                        LEFT JOIN (
                            SELECT d.district_id, d.district, d.identifier, ud.user_id
                            FROM user_districts ud JOIN districts d ON(ud.district_id = d.district_id)
                            ) d ON (u.user_id = d.user_id)
                        GROUP BY u.user_id
                        ORDER BY a.agency, full_name;";
                        $table = show(array('sql'=>$sql,'pkey'=>'user_id','table'=>'users',
                          'edit_function'=>'User.adminForm',
                          'include_delete'=>true,'delete_function'=>'User.delete',
                          'paginate'=>true,'no_results_message'=>'There are currently no users.',
                          'no_results_class'=>'info'));
                        echo $table['html'];
                    ?>
                    <button class="btn btn-sm btn-default" onclick="User.adminForm()">New User</button>
                </div>
            </div>
        </div>
    </div>
</body>
