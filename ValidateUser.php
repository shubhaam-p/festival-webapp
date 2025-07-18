<?php
    try {
        $IPExists = $USERID = $thankYouScreen = 0;
        $dataOfUploadedFiles = [];

        if($adminAccess){
            error_log("Admin");
            $main_dvo->USERID = $CONST_ADMIN_UID;
            return;
        }
        //Check if cookie is set of form submitted
        if (isset($_COOKIE['form_submitted'])) {
            error_log("cookie form_subitted is set".print_r($_COOKIE,1));
            throw new Exception("Cookie is set and form is submitted.");
        }

        //Check IP exists in DB
        $main_dvo->IPADDR = $functions->getUserIP();
        [$IPExists, $USERID] = $main_dao->checkIfIPExistsNGetUser($main_dvo);

        if(!empty($_COOKIE['userId']) || !empty($_SESSION['userId']) || !empty($IPExists)){
            //This will return the first defined value
            $main_dvo->USERID = (int)($USERID ?? $_SESSION['userId'] ?? $_COOKIE['userId'] ?? 0);
            
            //Get count of files uploaded
            $dataOfUploadedFiles = $main_dao->getCountOfUploadedFiles($main_dvo);

            if(!empty($IPExists)) {
               error_log("IP address is found and form is submitted. $IPExists, $USERID");
            }
            
            if(isset($dataOfUploadedFiles[3]) && $dataOfUploadedFiles[3] >=4)
                throw new Exception("Session is present or IP address is found, max files (count: $dataOfUploadedFiles[3]) are uploaded and form is previously submitted. User Id - $main_dvo->USERID");
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $thankYouScreen = 1;
    }
?>