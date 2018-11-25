<?php
$page_title = "Group Manager / Utah.gov";
include '../checklogin.php';
echo checklogin(array('title'=>$page_title));

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
                <h1>Group Manager</h1>
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
                <h4>Agencies</h4>
                <hr>
                <?php
                    $sql = "SELECT a.agency_id, a.abbreviation as \"Abbreviation\", a.agency as \"Agency\", a.address as \"Address\", a.phone as \"Phone\", u.full_name as \"Agency Contact\"
                    FROM agencies a
                    LEFT JOIN users u ON (u.user_id = a.owner_id)
                    ORDER BY a.agency;";
                    $table1 = show(array('sql'=>$sql,'pkey'=>'agency_id','table'=>'agencies','include_new'=>true,'paginate'=>true));
                    echo $table1['html'];
                ?>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-sm-12">
                <h4>Districts</h4>
                <hr>
                <?php
                    $sql = "SELECT d.district_id, d.identifier as \"Designator\", d.old_identifier as \"Previous Designator\", d.district as \"District\", d.address as \"Address\", d.phone as \"Phone\", u.full_name as \"Primary Contact\"
                    FROM districts d
                    LEFT JOIN users u ON (u.user_id = d.owner_id)
                    ORDER BY d.district;";
                    $table2 = show(array('sql'=>$sql,'pkey'=>'district_id','table'=>'districts','include_new'=>true,'paginate'=>true));
                    echo $table2['html'];
                ?>
            </div>
        </div>
    </div>
</body>
