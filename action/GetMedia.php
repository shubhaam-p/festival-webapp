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
        // return format- array, 0 - all other data, 1 - image data
        $data = $main_dao->getImages($main_dvo);
    }
    if(count($data[0]) > 0 || count($data[1]) > 0){
        $totalCnt = count($data[0]) + count($data[1]);
        $returnArray = [
            'status' => 'success',
            'message' => "Fetched successfully!",
            'data' => $data[0],
            'imageArr' => ['data'=>$data[1], 'total'=>count($data[1])],
            'total' => $totalCnt,
        ];
    }else{
        $returnArray = [
            'status' => 'error',
            'message' => "Data not found!",
            'data' => $data,
            'total' => 0
        ];
    }

    echo json_encode($returnArray);
    exit();
?>