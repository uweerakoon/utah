<?php
$page_title = "Form 3: Pre-Burn Reviewer / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/pre_burn_review.js\"></script>
            <script type=\"text/javascript\" src=\"../js/pre_burn.js\"></script>
            <script type=\"text/javascript\" src=\"../js/filter.js\"></script>
            <script type=\"text/javascript\" src=\"../js/gfilter.js\"></script>
            <script>
                PreBurnReview = new PreBurnReview();
                PreBurn = new PreBurn();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$review = new \Manager\PreBurnReview($db);

if ($_GET['pre_burn'] == true) {
    $main = $review->reviewPage($_GET['id']);
} else {
    $title = "Reviewer <small>Form 3: Pre-Burns</small>";
    $head = $review->getReviewMap();
    $main = $review->reviewTable();
    $side_bar = $review->sidebar();

    $main = "<div class=\"row\">
        <div class=\"col-sm-12\">
            <h3>$title</h3>
        </div>
    </div>
    <div class=\"row\">
        <div class=\"col-sm-3\">
            $side_bar
        </div>
        <div class=\"col-sm-9\">
            $head
            $main
        </div>
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
        <?php echo $main; ?>
    </div>