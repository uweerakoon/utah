<?php
$page_title = "Annual Registration / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/burn_review.js\"></script>
            <script type=\"text/javascript\" src=\"../js/filter.js\"></script>
            <script>
                //BurnProjectReview = new BurnProjectReview();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$review = new \Manager\AnnualReview($db);

if ($_GET['burn'] == true) {
    $main_class = "col-sm-8";
    $burn_plan_id = $_GET['id'];
    $burn_plan = $review->get($burn_plan_id);
    $main = $review->reviewPage($burn_plan_id);
    $title = $burn_plan['values']['base']['burn_number']." <small>Burn Plan</small>";
    $title_status = $review->getStatus($burn_plan_id);
    $side_bar = $review->getSidebar($burn_plan_id);
    $return_href = $review->mainUrl();

    $body = "<div class=\"col-sm-8\">
            <h4>Details</h4>
            <hr>
            $main
        </div>
        
            $side_bar
        ";
} else {
    $title = "Reviewer <small>Registered Burn Plans</small>";
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