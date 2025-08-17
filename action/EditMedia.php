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

    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();

    $returnArray = [];
    $error = $msg = $msg1 = $fileName = '';
    $res = $remove = $main_dvo->UNIQUEID = 0;

    if(isset($_GET['admin']) && $_GET['admin'] != '') {
        $isAdminPage = $functions->sanitizeInput($_GET['admin']);
    }
 
    if(isset($_GET['nid']) && $_GET['nid'] != '') {
        $main_dvo->UNIQUEID = $functions->sanitizeInput($_GET['nid']);
    }

    if(isset($_GET['remove']) && $_GET['remove'] != '') {
        $remove = $functions->sanitizeInput($_GET['remove']);
    }
    
    if(($remove == 1) && $main_dvo->UNIQUEID == 0){
        $returnArray['status'] = '0';
        $returnArray['error'] = '1';
        $returnArray['msg'] = 'Media id is not valid';
        echo json_encode($returnArray);
        exit();
    }

    try {
        $action = $remove? "Removing":"Updating";
        $msg = "Error while $action data $msg1";

        // After deleting the media, change its status to 3
        if($remove == 1){
            $res = $main_dao->getMediaById($main_dvo);
            if(count($res) <= 0)
                throw new Exception("Data not found", 1);

            $mediaFile = $res['MEDIA'];
            $arr = explode('/', $mediaFile);
            if(isset($arr[count($arr)-1]))
                $fileName = $_SERVER['DOCUMENT_ROOT'].'/uploads/'. $arr[count($arr)-1];
            else
                throw new Exception("Media URL is not proper -> '{$mediaFile}'", 1);

            if (file_exists($fileName)) {
                if (unlink($fileName)) {
                    $res = $main_dao->changeStatus($main_dvo, 3);
                    if($res)
                        $msg = 'Removed successfully';
                    else
                        throw new Exception("Error while updating status", 1);
                } else {
                    throw new Exception("Error: Could not delete file '{$fileName}' '{$main_dvo->UNIQUEID}'.", 1);
                }
            } else {
                throw new Exception("Error: File '{$fileName}' '{$main_dvo->UNIQUEID}' does not exist.", 1);
            }

            $returnArray = [
                'status'=> 1,
                'msg'=> $msg
            ];
        }else{
            throw new Exception("Request is not processed, provide parameter.", 1);
        }

    } catch (Exception $e) {
        error_log("Exception :: ".$e->getMessage());
        $returnArray = [
                'status'=> 2,
                'msg'=> $msg,
                'error'=> $e->getMessage()
        ];
    }

    echo json_encode($returnArray);
    exit();
?>