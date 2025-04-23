<?php
    include "../Constants.php";
    include '../dvo/MAIN-DVO.php';
    include '../dao/MAIN-DAO.php';
    include '../Functions.class.php';

    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();

// error_log("xmlData ".print_r($_REQUEST["xmlData"],1)." -- ".__DIR__ );
    $returnArray = $data = [];

    $main_dvo->LIMIT = $CONST_FETCH_IMAGE_LIMIT;
    $data = $main_dao->getImages($main_dvo);
    if(count($data) > 0){
        $returnArray = [
            'status' => 'success',
            'message' => "Fetched successfully!",
            'data' => $data
        ];
    }else{
        $returnArray = [
            'status' => 'error',
            'message' => "Data not found!",
            'data' => $data
        ];
    }

    echo json_encode($returnArray);
    // echo $msg;
    exit();

        //&lt;?xml version='1.0'?&gt; &lt;query id='addPackageDetails'&gt;&lt;author&gt;name&lt;/author&gt;&lt;/query&gt;
?>