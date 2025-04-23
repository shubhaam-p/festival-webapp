<?php

class Functions{  
    function sanitizeInput($str) {
        if (!$str)
            return false;
        return htmlentities(trim(strip_tags($str)), ENT_NOQUOTES, 'UTF-8');
    }
}
?>