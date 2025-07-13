<?php
    try {
        $IPExists = 0;
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
        $IPExists = $main_dao->checkIfIPExists($main_dvo);

        if(isset($_COOKIE['userId']) || isset($_SESSION['userId'])){
            //Get count of files uploaded
            $main_dvo->USERID = isset($_COOKIE['userId'])? $_COOKIE['userId'] : $_SESSION['userId'];
            $dataOfUploadedFiles = $main_dao->getCountOfUploadedFiles($main_dvo);

            if(isset($dataOfUploadedFiles[3])  && $dataOfUploadedFiles[3] >=4)
                throw new Exception("Session is present, max files (count: $dataOfUploadedFiles[3]) are uploaded and form is previously submitted. User Id - $main_dvo->USERID");
        }else if (!empty($IPExists)) {
            throw new Exception("IP address is found and form is submitted.");
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        die("<h3>You have already submitted this form.</h3>");
    }
?>