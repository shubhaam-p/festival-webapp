<?php
    try {
        //Check if cookie is set of form submitted
        if (isset($_COOKIE['form_submitted'])) {
            error_log("cookie form_subitted is set".print_r($_COOKIE,1));
            throw new Exception("Cookie is set and form is submitted.");
        }

        //Check IP exists in DB
        $IPExists = $countOfUploadedFiles = 0;
        $main_dvo->IPADDR = $functions->getUserIP();
        $IPExists = $main_dao->checkIfIPExists($main_dvo);
        error_log("ip add".$main_dvo->IPADDR);
        if(isset($_COOKIE['media_count']) && $_COOKIE['media_count'] >=4){
            //do nothing and submit the form
        }
        
        if(isset($_SESSION['userId'])){
            //Get count of files uploaded
            $main_dvo->USERID = $_SESSION['userId'];
            $countOfUploadedFiles = $main_dao->getCountOfUploadedFiles($main_dvo);
            if($countOfUploadedFiles >=4)
                throw new Exception("Session is present, max files (count: $countOfUploadedFiles) are uploaded and form is submitted. User Id - $main_dvo->USERID");
        }else if (!empty($IPExists)) {
            throw new Exception("IP address is found and form is submitted.");
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        die("<h3>You have already submitted this form.</h3>");
    }
?>