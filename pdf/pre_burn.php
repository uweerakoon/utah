<?php
include "../checklogin.php";
include "../mpdf/mpdf.php";
$header = checklogin(array('suppress_errors'=>true, 'suppress_nav'=>true,'page_check'=>false));

$mpdf = new \mPDF('c', 'A4-L');
$pre_burn = new \Manager\PreBurn($db);

if (isset($_GET['id'])) {
    $content = $pre_burn->pdfPage($_GET['id']);
    $stylesheet = file_get_contents('/var/www/css/style.css');

    ob_clean();
    
    $mpdf->WriteHTML($stylesheet, 1);
    $mpdf->WriteHTML($content, 2);
    $mpdf->Output();
    exit;
}
