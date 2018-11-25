<?php
$page_title = "Info Manager/Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/index.js\"></script>
            <script>
                HomeManager = new HomeManager();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,));

if (isset($_POST['my'])) {
    $save = $_POST['my'];
    echo code($save_array);
}
?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="event-block">
                    <? echo $message; ?>
                </div>
                <h3>Manager <small>Home Page</small></h3>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-block">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h5>Daily Home Page Content</h5>
                <?php
                    $sql = "SELECT i.index_id, i.date as \"Date\", CASE WHEN i.html IS NOT NULL THEN \"Edit to View\" ELSE \"No HTML Specified\" END as \"Content\", COALESCE(u.full_name, a.full_name) as \"Last Edited By\"
                    FROM `index` i
                    LEFT JOIN `users` a ON(a.user_id = i.added_by)
                    LEFT JOIN `users` u ON(u.user_id = i.updated_by)
                    ORDER BY `date`;";
                    $table1 = show(array('sql'=>$sql,'pkey'=>'index_id','table'=>'index','include_new'=>true,'new_function'=>'HomeManager.form()',
                        'paginate'=>true,'edit_function'=>'HomeManager.updateForm','include_edit'=>true));
                    echo $table1['html'];
                ?>                
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-sm-12">
                <h5>Preparedness Level</h5>
                <?php
                    $sql = "SELECT i.index_level_id, i.date as \"Date\", i.egbc_level as \"EGBC Level\", i.national_level as \"National Level\", COALESCE(u.full_name, a.full_name) as \"Last Edited By\"
                    FROM `index_levels` i
                    LEFT JOIN `users` a ON(a.user_id = i.added_by)
                    LEFT JOIN `users` u ON(u.user_id = i.updated_by)
                    ORDER BY `date`;";
                    $table1 = show(array('sql'=>$sql,'pkey'=>'index_level_id','table'=>'index_levels','include_new'=>true,'new_function'=>'HomeManager.formLevels()',
                        'paginate'=>true,'edit_function'=>'HomeManager.updateFormLevels','include_edit'=>true));
                    echo $table1['html'];
                ?>                
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-sm-6">
                <h5>GBCC Team Fires</h5>
                <?php
                    $sql = "SELECT i.gbcc_team_fire_id, i.date as \"Date\", i.html as \"GBCC Team Fires\", COALESCE(u.full_name, a.full_name) as \"Last Edited By\"
                    FROM `gbcc_team_fires` i
                    LEFT JOIN `users` a ON(a.user_id = i.added_by)
                    LEFT JOIN `users` u ON(u.user_id = i.updated_by)
                    ORDER BY `date`;";
                    $table1 = show(array('sql'=>$sql,'pkey'=>'gbcc_team_fire_id','table'=>'gbcc_team_fires',
                        'include_new'=>true,'new_function'=>'HomeManager.formTeamFires()',
                        'paginate'=>true,'edit_function'=>'HomeManager.updateFormTeamFires','include_edit'=>true));
                    echo $table1['html'];
                ?>                
            </div>
            <div class="col-sm-6">
                <h5>GBCC Large Fires</h5>
                <?php
                    $sql = "SELECT i.gbcc_large_fire_id, i.date as \"Date\", i.html as \"GBCC Large Fires\", COALESCE(u.full_name, a.full_name) as \"Last Edited By\"
                    FROM `gbcc_large_fires` i
                    LEFT JOIN `users` a ON(a.user_id = i.added_by)
                    LEFT JOIN `users` u ON(u.user_id = i.updated_by)
                    ORDER BY `date`;";
                    $table1 = show(array('sql'=>$sql,'pkey'=>'gbcc_large_fire_id','table'=>'gbcc_large_fires',
                        'include_new'=>true,'new_function'=>'HomeManager.formLargeFires()',
                        'paginate'=>true,'edit_function'=>'HomeManager.updateFormLargeFires','include_edit'=>true));
                    echo $table1['html'];
                ?>                
            </div>
        </div>