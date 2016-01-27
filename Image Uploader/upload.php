<?php

require_once('upload.class.php');

$strUploadPath = 'files/';
$objSIU = new SIU($strUploadPath);
$arrSize = array('width' => 200, 'height' => 200);
if($objSIU->SetMaxDimensions($arrSize)) {
    $arrData = $objSIU->SafeSave();
    if($arrData['success']) {
        echo $arrData['filename'];
    } else {
        echo 'Error Status: ' . $arrData['status'] . ' - ' . $arrData['error'];
    }
}


?>