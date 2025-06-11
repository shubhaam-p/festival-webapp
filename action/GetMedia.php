<?php
    if(!isset($_GET['Type']) || $_GET['Type'] != 'Gall3ry') {
        $returnArray['save'] = 0;
        $returnArray['error'] = 1;
        $returnArray['msg'] = 'Invalid Request';
        echo json_encode($returnArray);
        exit();
    }

    include "../Constants.php";
    include '../dvo/MAIN-DVO.php';
    include '../dao/MAIN-DAO.php';
    include '../Functions.class.php';
    
    //Can use this to list media for admin
    $isHomePage = '';
    $returnArray = $data = [];
    
    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();
    
    if(isset($_GET['home']) && $_GET['home'] != '') {
        $isHomePage = $functions->sanitizeInput($_GET['home']);
    }

    if(!empty($isHomePage)){
        $main_dvo->LIMIT = $CONST_FETCH_IMAGE_LIMIT;
        $data = $main_dao->getImages($main_dvo);
    }
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
    exit();
?>