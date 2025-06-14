<?php

    include "ConstantDomainConfigs.php";
    $CONST_DOMAIN_THEME_COLOR = "#00F";
    $ENCRYPT_DECRYPT_KEY = '!@#oncontract$%^123ABC456DEF7890';
    $CONST_REPLACE_STR = 'CAAS';
    define('CONST_REPLACE_STR', $CONST_REPLACE_STR);
    $COOKIEDOMAIN = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    $VER = '0.1';

    $CONST_FETCH_IMAGE_LIMIT = 10;

    $CONST_ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'mp4'];
    $CONST_TYPE_IMAGE = ['jpg', 'jpeg', 'png'];
    $CONST_TYPE_OTHER_MEDIA = ['gif', 'mp3', 'mp4'];
    
    $CONST_ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'audio/mpeg', 'audio/mp4'];
    $CONST_MIME_TYPE_IMAGE = ['image/jpeg', 'image/png'];
    $CONST_MIME_TYPE_OTHER_MEDIA = ['image/gif', 'audio/mpeg', 'video/mp4'];

    $STATUS_FIELD_IN_RESPONSE = [
        '1'=>'Success',
        '2'=>'Error'
    ];

    $TYPE_COLUMN_IN_MEDIA_TABLE = [
        '1'=>'Image',
        '2'=>'Video, gif, audio'
    ];

    $MAX_FILE_COUNT_FOR_EACH_TYPE = 2;

    $COOKIE_EXPIRY_TIME = 3600 * 30; //time is in seconds 

    $MAX_FILE_SIZE_IMAGE = 500 * 1024; // 500 KB
    $MAX_FILE_SIZE_OTHER_MEDIA = 1000 * 1024; // 1Mb

    $MAX_POST_SIZE = 4000 * 1024; // 4 MB limit ***not in use**

?>  