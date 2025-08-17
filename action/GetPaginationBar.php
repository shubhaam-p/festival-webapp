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
    $isAdminPage = '';
    $returnArray =  [];
    $page = 1;
    
    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();

    if(isset($_GET['admin']) && $_GET['admin'] != '') {
        $isAdminPage = $functions->sanitizeInput($_GET['admin']);
    }

    if($CONST_PAGINATION_STATUS === 1 && !empty($_GET['page']) && $_GET['page'] != '') {
        $main_dao->page = intval($functions->sanitizeInput($_GET['page']));
    }

    $str = 'listAdminMediaFiles(`page~pagenum~`,`1`);';
    $res = $main_dao->renderFullNav($str);
    if($res){
        $returnArray = [
            'status' => 1,
            'message' => "Fetched successfully!",
            'data' => $res,
            'page' => $page,
        ];
    }else{
        $returnArray = [
            'status' => 0,
            'message' => "Data not found!"
        ];
    }

    echo json_encode($returnArray);
    exit();
?>