<?php
$page_title = "Form 2: Burn Project / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/project.js\"></script>
            <script>
                BurnProject = new BurnProject();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$write = checkFunctionPermissions($_SESSION['user']['id'], array('user','user_district','user_agency'), 'write');

$temp_project = new \Manager\BurnProject($db);
$user = new \Info\User($db);
$agency_id = $_SESSION['user']['agency_id'];

if ($user->hasAgency($_SESSION['user']['id'])) {
    if ($_GET['detail'] == true) {
        // Detailed view of daily request.
        $html = $temp_project->detailPage($_GET['id']);
        $interface = $html['main'];
    } else {
        $html = $temp_project->overviewPage();
 
        if ($write['any']) {  
            $on_click = "BurnProject.newForm()";

            $new_btn = "<div class=\"pull-right\">
                <button class=\"btn btn-sm btn-default\" onclick=\"$on_click\">New Burn Project</button>
            </div>";

            $write_li = "<li role=\"presentation\" class=\"active\"><a href=\"#tableEdit\" aria-controls=\"tableEdit\" role=\"tab\" data-toggle=\"tab\">Editable Projects</a></li>";
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
                    $('#projectTabs a[role=\"tab\"]').on( \"click\", function(event) {
                        event.preventDefault()
                        $(this).tab('show')
                        resizeMap(map)
                    });
                });
            </script>
            <div id=\"projectTabs\" role=\"tabpanel\">
                <ul class=\"nav nav-tabs\" role=\"tablist\">
                    $write_li
                    <li role=\"presentation\" class=\"$view_active\"><a href=\"#tableTab\" aria-controls=\"tableTab\" role=\"tab\" data-toggle=\"tab\">District-Wide Projects</a></li>
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

if ($_GET['form'] == true) {
    $script = "<script>
            $(document).ready(function() {
                $on_click
            });
        </script>"; 
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
    <?php echo $script; ?>