<?php
include 'checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/public_map.js\"></script>
            <script>
                PublicMap = new PublicMap();
            </script>";
echo checklogin(array('title'=>'Burn Map / Utah.gov','public'=>true,'extra_js'=>$extra_js,'api'=>'map'));

$publicMap = new \PublicZone\publicMap($db);

if ($_GET['detail']) {
    $html = $publicMap->detailPage($_GET['id']);
} else {
    $html['header'] = "<div class=\"btn-group pull-right\">
                    <button class=\"btn btn-sm btn-default\" onclick=\"PublicMap.filterForm()\">Filter Results</button>
                </div>
                <h3>Approved Burns</h3>
                <hr>";

    $html['main'] = $publicMap->map();
}

?>
<style type="text/css">
    body {
        padding-top: 64px;
    }
</style>

<div class="container">
    <div class="row" style="min-height: 256px;">
        <div class="col-sm-12">
            <div id="general">
                <?php echo $html['header']; ?>    
                <?php echo $html['main']; ?>
            </div>
        </div>
    </div>
</div>