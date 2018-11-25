<?php
$page_title = "Form 2: Burn Project Reviewer / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/project_review.js\"></script>
            <script type=\"text/javascript\" src=\"../js/project.js\"></script>
            <script type=\"text/javascript\" src=\"../js/filter.js\"></script>
            <script>
                BurnProject = new BurnProject();
                BurnProjectReview = new BurnProjectReview();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$review = new \Manager\BurnProjectReview($db);

if ($_GET['detail'] == true) {
    $main_class = "col-sm-8";
    $burn_project_id = $_GET['id'];
    $burn_project = $review->get($burn_project_id);
    $main = $review->reviewPage($burn_project_id);
    $title = $burn_project['project_name']. " - ".$burn_project['project_number']." <small>Burn Project</small>";
    $title_status = $review->getStatus($burn_project_id);
    $side_bar = $review->getSidebar($burn_project_id);
    $return_href = $review->mainUrl();

    $body = "<div class=\"col-sm-8\">
            <h4>Form 2: Burn Project Info</h4>
            <hr>
            $main
        </div>
            $side_bar
        ";
} else {
    $title = "Reviewer <small>Form 2: Burn Projects</small>";
    $main = $review->reviewTable();
    $side_bar = $review->sidebar();

    $body = "<div class=\"col-sm-3\">
            $side_bar
        </div>
        <div class=\"col-sm-9\">
            $main
        </div>
    ";
}

?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <?php echo $error; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <span class="pull-right">
                    <?php 
                    echo $return_href;
                    echo $title_status;
                    ?>
                </span>
                <h3 class=""><?php echo $title; ?></h3>
            </div>
        </div>
        <br>
        <div class="row">
            <?php echo $body; ?>
        </div>
    </div>