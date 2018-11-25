<?php
$page_title = "Form 4: Burn Request Manager / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/burn.js\"></script>
            <script>
                Burn = new Burn();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$write = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');

$temp_burn = new \Manager\Burn($db);
$user = new \Info\User($db);

if ($user->hasAgency($_SESSION['user']['id'])) {
    // Page toggles.

    if ($_GET['burn'] == true) {
        // Detailed view of daily request.
        $html = $temp_burn->detailPage($_GET['id']);
        $interface = $html['main'];
    } 
       // elseif ($_GET['burn_project'] == true) {
       // // Table of all daily burns associated with burn plan id.
       // $html = $temp_burn->overviewPage(array('burn_plan_id'=>$_GET['id']));
     else {
        $html = $temp_burn->overviewPage();
        
        if ($write['any']) {  
            $on_click = "Burn.newForm()";
    
            $new_btn = "<div class=\"pull-right\">
                    <button class=\"btn btn-sm btn-default\" onclick=\"$on_click\">New Burn Request</button>
                </div>";
            
            $write_li = "<li role=\"presentation\" class=\"active\"><a href=\"#tableEdit\" aria-controls=\"tableEdit\" role=\"tab\" data-toggle=\"tab\">Editable Burn Requests</a></li>";
            $write_block = "<div role=\"tabpanel\" class=\"tab-pane active\" id=\"tableEdit\">
                        <br>
                        {$html['edit_table']}
                    </div>";
            $view_active = "";
        } else {
            $view_active = "active";
        }

        $interface = "<script type=\"text/javascript\">
                function resizeMap(map) {
                    google.maps.event.trigger(map, 'resize');
                    map.setCenter(new google.maps.LatLng($map_center));
                };

                $(document).ready(function() {
                    $('#burnTabs a[role=\"tab\"]').on( \"click\", function(event) {
                        event.preventDefault()
                        $(this).tab('show')
                        resizeMap(map)
                    });
                });
            </script>
            <div id=\"burnTabs\" role=\"tabpanel\">
                <ul class=\"nav nav-tabs\" role=\"tablist\">
                    $write_li
                    <li role=\"presentation\" class=\"$view_active\"><a href=\"#tableTab\" aria-controls=\"tableTab\" role=\"tab\" data-toggle=\"tab\">District-Wide Burn Requests</a></li>
                    <li role=\"presentation\"><a href=\"#mapTab\" aria-controls=\"mapTab\" role=\"tab\" data-toggle=\"tab\">Map</a></li>
                    <span class=\"pull-right\">$new_btn</span>
                </ul>
                <div class=\"tab-content\">
                $write_block
                    <div role=\"tabpanel\" class=\"tab-pane $view_active\" id=\"tableTab\">
                        <br>
                        {$html['view_table']}
                    </div>
                    <div role=\"tabpanel\" class=\"tab-pane\" id=\"mapTab\">
                        <br>
                        {$html['map']}
                    </div>
                </div>
            </div>";

    }
} else {
    // No agency detected.
    $error = status_message("Your user must be associated with an Agency, please contact Utah.gov.", "error");
}

?>
    <div class="container" style="margin-bottom: 15px">
        <div class="row">
            <div class="col-sm-12">
                <?php echo $error;
                echo $toggle; ?>
            </div>
        </div>
        <?php echo $html['header']; ?>
        <br>
        <div id="interfaceForm" style="display:none">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-block"></div>
                </div>
            </div>
        </div>
        <div id="interfaceMain">
            <?php echo $interface; ?>
        </div>
    </div>