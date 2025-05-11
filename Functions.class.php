<?php

class Functions{  
    function sanitizeInput($str) {
        if (!$str)
            return false;
        return htmlentities(trim(strip_tags($str)), ENT_NOQUOTES, 'UTF-8');
    }

    function validateFileNTypes($main_dvo, $CONST_TYPE_IMAGE, $CONST_TYPE_OTHER_MEDIA){
        $imageCount = $otherMediaCount = 0;

        foreach($main_dvo->FILEARR as $key => $value){
            //Sanitie file name
            $fn = $this->sanitizeInput(preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($value)));

            if(in_array(pathinfo($fn, PATHINFO_EXTENSION), $CONST_TYPE_IMAGE))
                $imageCount++;
            else if(in_array(pathinfo($fn, PATHINFO_EXTENSION), $CONST_TYPE_OTHER_MEDIA))
                $otherMediaCount++;

            $main_dvo->FILEARR[$key] = $value;
        }

        return [$imageCount, $otherMediaCount];
    }

    function getUserIP() {
        return $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR'];
    }
}
?>