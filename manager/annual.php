<?php
$page_title = "Annual Registration / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/burn.js\"></script>
            <script type=\"text/javascript\" src=\"../js/filter.js\"></script>
            <script>
                BurnProject = new BurnProject();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$temp_plan = new \Manager\BurnProject($db);
$user = new \Info\User($db);

if ($_GET['form'] == true) {
	$script = "<script>
			$(document).ready(function() {
				BurnProject.registerSelect();
			});
		</script>";	
}

if ($user->hasAgency($_SESSION['user']['id'])) {
    // Count user districts.
    $district_count = $user->countDistricts($_SESSION['user']['id']);

    if ($district_count > 1 && $_GET['detail'] == false) {
        // Add a district toggle button if the user is associated with more than one.
        $toggle = $user->districtToggle($_SESSION['user']['id'], $_GET['district_id']);
    }

    if ($_GET['detail'] == true) {
        // Detailed view of daily request.
        $html = $temp_plan->detailPage($_GET['id']);
    } elseif ($_GET['district_id'] > 0) {
        // Table of all daily burns.
        $html = $temp_plan->annualOverviewPage(array('district_id'=>$_GET['district_id']));
    } elseif ($district_count == 1) {
        $html = $temp_plan->annualOverviewPage(array('district_id'=>$_SESSION['user']['districts'][0]['id']));
    } else {
        $html = $temp_plan->annualOverviewPage();
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
        <div class="row">
            <div class="col-sm-12">
                <div class="form-block"></div>
            </div>
        </div>
        <?php 
        	echo $html['main']; 
	        echo $script;
        ?>
    </div>