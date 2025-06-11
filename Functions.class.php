<?php

class Functions{  
    function sanitizeInput($str) {
        if (!$str)
            return false;
        return htmlentities(trim(strip_tags($str)), ENT_NOQUOTES, 'UTF-8');
    }

    function validateFileNTypes($main_dvo, $CONST_MIME_TYPE_IMAGE, $CONST_MIME_TYPE_OTHER_MEDIA){
        $imageCount = $otherMediaCount = 0;
        $mimeArr = $notAllowedMedia = [];

        foreach($main_dvo->TEMPFNAME as $key => $value){            

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $value);
            finfo_close($finfo);
            $mimeArr[$key] = $mimeType;

            if(in_array($mimeType, $CONST_MIME_TYPE_IMAGE))
                $imageCount++;
            else if(in_array($mimeType, $CONST_MIME_TYPE_OTHER_MEDIA))
                $otherMediaCount++;
            else
                $notAllowedMedia[$key] = $mimeType;
        }
        //count of Images, otherType(Video, audio, GIF), Mime Array for storing, not allowed media for throwing the error.
        return [$imageCount, $otherMediaCount, $mimeArr, $notAllowedMedia];
    }

    function getUserIP() {
        return $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR'];
    }

    function getMediaDimensions($filePath, $mimeType, $videoDetails = []){
        $details = [];
        $type = 0;
        switch (explode('/', $mimeType)[0]) {
            case 'image':
                    $details = getimagesize($filePath);
                    $type = 1; 
                break;
                case 'video':
                    $details = $videoDetails;
                    $type = 2; 
                break;
            // case 'audio':
            // @todo
            //     Using ID3 library, see if its required and more imp is, if it works on server. 
            //     break;
            default:
                echo "Unsupported format";
        }
        return [$type, $details];
    }
}
?>