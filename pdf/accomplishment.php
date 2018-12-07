<?php
include '../checklogin.php';
include '../mpdf/mpdf.php';
$header = checklogin(array('suppress_errors'=>true,'suppress_nav'=>true,'page_check'=>false));

$mpdf = new \mPDF('c', 'A4-L');
$accomplishment = new \Manager\Accomplishment($db);

if (isset($_GET['id'])) {
    $content = $accomplishment->pdfPage($_GET['id']);
    $stylesheet = file_get_contents('/Users/udaraweerakoon/managedisaster/managedisastersource/utah/css/style.css');

    ob_clean();

    $mpdf->WriteHTML($stylesheet, 1);
    $mpdf->WriteHTML($content, 2);
    $mpdf->Output();
    exit;
}
