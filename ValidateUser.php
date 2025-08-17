<?php
    try {
        $IPExists = $USERID = $thankYouScreen = 0;
        $dataOfUploadedFiles = [];

        //**No limit to upload to admin -  Do not validate admin user
        if(!empty($key)){
            if(isset($MAP_KEY_TO_ADMIN[$key])){
                $main_dvo->USERID = $MAP_KEY_TO_ADMIN[$key];
                $adminAccess = 1;
            }else 
                throw new Exception("Error 403 - Forbidden", 403);

            $date = date_create("now",timezone_open("Asia/Kolkata"));
            $date = date_format($date, "Y-m-d H:i:s");
            error_log("Admin access : ".$main_dvo->USERID ." -- ".$date);
            return;
        }
        //Check if cookie is set of form submitted
        if (isset($_COOKIE['form_submitted'])) {
            error_log("cookie form_subitted is set".print_r($_COOKIE,1));
            throw new Exception("Cookie is set and form is submitted.", 200);
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
               error_log("IP address is found and form is submitted. $IPExists, $USERID", 200);
            }
            
            if(isset($dataOfUploadedFiles[3]) && $dataOfUploadedFiles[3] >=4)
                throw new Exception("Session is present or IP address is found, max files (count: $dataOfUploadedFiles[3]) are uploaded and form is previously submitted. User Id - $main_dvo->USERID", 200);
        }
    } catch (Exception $e) {
        error_log($e->getMessage()." -- ".$e->getCode());
        if($e->getCode() == 200)
            die(require_once $_SERVER['DOCUMENT_ROOT'] . '/views/user/thankYouScreen.php');
        else
            die("<h1>".$e->getMessage()."</h1>");
    }
?>