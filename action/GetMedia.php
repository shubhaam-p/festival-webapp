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
    $returnArray = $data = $scannedData =  [];
    $page = 1;
    
    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();
    
    if(isset($_GET['home']) && $_GET['home'] != '') {
        $isHomePage = $functions->sanitizeInput($_GET['home']);
    }

    if(isset($_GET['home']) && $_GET['home'] != '') {
        $isHomePage = $functions->sanitizeInput($_GET['home']);
    }

    if($CONST_PAGINATION_STATUS === 1 && !empty($_GET['page']) && $_GET['page'] != '') {
        $page = $functions->sanitizeInput($_GET['page']);
        $main_dvo->OFFSET = ($page - 1) * ($CONST_FETCH_IMAGE_LIMIT-1);
        $main_dvo->PAGINATION = $CONST_PAGINATION_STATUS;
    }

    if(!empty($isHomePage)){
        $main_dvo->LIMIT = $CONST_FETCH_IMAGE_LIMIT;
        
        //Returns media array and last id of media
        $data = $main_dao->getImages($main_dvo, $functions);
    }

    if(count($data) > 0){
        $totalCnt = count($data);

        $isLastPage = $functions->isLastPage($totalCnt, ($CONST_FETCH_IMAGE_LIMIT-1));        
        //Remove the 11th record before shuffle
        if(empty($isLastPage)){
            array_pop($data);
        }

        //Removes media which doesn't exist
        foreach($data as $item){
            if(isset($item['MEDIA']) && !empty($functions->isFileExits($item['MEDIA']))){
                array_push($scannedData, $item);
            }else
                error_log("File doesn't exist - 'ID'=>".$item['ID'].", 'MEDIA'=>". $item['MEDIA']."");
        }

        //@todo - Add block to fetch files again, if array count is less than 5, after scanning them above.

        //To get new media each time
        shuffle($scannedData);

        //Add last page here only
        if(!empty($isLastPage)){
            array_push($scannedData, array('ID'=>0, 'MIMETYPE'=>'last/slide'));
        }

        // Re-calculate after poppping last element & if last page is added.
        $data = $scannedData;
        $totalCnt = count($data);

        $returnArray = [
            'status' => 'success',
            'message' => "Fetched successfully!",
            'data' => $data,
            'total' => $totalCnt,
            'page' => $page,
            'lastPage'=> $isLastPage
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