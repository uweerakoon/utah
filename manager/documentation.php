<?php
$page_title = "Form 9: Burn Documentation Manager / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/documentation.js\"></script>
            <script>
                BurnDocumentation = new BurnDocumentation();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$write = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');

$temp_burn = new \Manager\BurnDocumentation($db);
$user = new \Info\User($db);

if ($user->hasAgency($_SESSION['user']['id'])) {
    // Page toggles.

    if ($_GET['detail'] == true) {
        // Detailed view of daily request.
        $html = $temp_burn->detailPage($_GET['id']);
        $interface = $html['main'];
    }
    // elseif ($_GET['burn_project'] == true) {
    //    // Table of all daily burns associated with burn plan id.
    //    $html = $temp_burn->overviewPage(array('burn_project_id'=>$_GET['id']));
     else {
        $html = $temp_burn->overviewPage();

        if ($write['any']) {  
            $on_click = "BurnDocumentation.newForm()";

            $new_btn = "<div class=\"pull-right\">
                    <button class=\"btn btn-sm btn-default\" onclick=\"$on_click\">New Burn Documentation Form</button>
                </div>";

            $write_li = "<li role=\"presentation\" class=\"active\"><a href=\"#tableEdit\" aria-controls=\"tableEdit\" role=\"tab\" data-toggle=\"tab\">Editable Documentation</a></li>";
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
                    $('#documentationTabs a[role=\"tab\"]').on( \"click\", function(event) {
                        event.preventDefault()
                        $(this).tab('show')
                        resizeMap(map)
                    });
                });
            </script>
            <div id=\"documentationTabs\" role=\"tabpanel\">
                <ul class=\"nav nav-tabs\" role=\"tablist\">
                    $write_li
                    <li role=\"presentation\" class=\"$view_active\"><a href=\"#tableTab\" aria-controls=\"tableTab\" role=\"tab\" data-toggle=\"tab\">District-Wide Documentation</a></li>
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

    global $map_center;
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