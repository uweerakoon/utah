<?php
$page_title = "Form 5: Burn Accomplishment Reviewer / Utah.gov";
include '../checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/accomplishment_review.js\"></script>
            <script type=\"text/javascript\" src=\"../js/accomplishment.js\"></script>
            <script type=\"text/javascript\" src=\"../js/filter.js\"></script>
            <script type=\"text/javascript\" src=\"../js/gfilter.js\"></script>
            <script>
                AccomplishmentReview = new AccomplishmentReview();
                Accomplishment = new Accomplishment();
            </script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,'api'=>'map'));

$review = new \Manager\AccomplishmentReview($db);

if ($_GET['detail'] == true) {
    $main = $review->reviewPage($_GET['id']);
} else {
    $title = "Reviewer <small>Form 5: Burn Accomplishments</small>";
    $map = $review->getReviewMap();
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
            $map
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