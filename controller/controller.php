<?php

$maxPostSize = 4000 * 1024; // 4 MB limit

if ($_SERVER['CONTENT_LENGTH'] > $maxPostSize) {
    // http_response_code(413); // Payload Too Big
    $returnArray = [
        'status'=>2,
        'desc-error'=>'Post Limit exceeded!!',
        'message'=> 'Total upload size exceeds, please check guidlines to upload media.'
    ];
    $a = json_encode($returnArray);
    die($a);
}

function getErrorCode($errno){
    return "ERR_"."FPSMEC_".$errno;
}

function myExceptionHandler($exception)
{
    echo "<?xml version='1.0'?><result><status>".getErrorCode(7).":".$exception->getMessage()."</status></result>";
}



###########
set_exception_handler('myExceptionHandler');

$arrActions=array();
$arrActions['addMedia'] 			= '../action/AddMedia.php';

$action = $_REQUEST['action'];
if($arrActions[$action]) {
    include $arrActions[$action];
} else {
    echo "<?xml version='1.0'?><result><status>".getErrorCode(12).":Page not found!</status></result>";
}
?>