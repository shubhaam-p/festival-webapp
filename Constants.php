<?php

    include "ConstantDomainConfigs.php";
    $CONST_DOMAIN_THEME_COLOR = "#00F";
    $ENCRYPT_DECRYPT_KEY = '!@#oncontract$%^123ABC456DEF7890';
    $CONST_REPLACE_STR = 'CAAS';
    define('CONST_REPLACE_STR', $CONST_REPLACE_STR);
    $COOKIEDOMAIN = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    $VER = '0.1';

    //status column
    $status = [
        '0'=>'new review',
        '1'=>'active', //package, review
        '2'=>'inactive', //package, review
        '3'=>'remove package, review',
    ];


    $CONST_FETCH_IMAGE_LIMIT = 10;

    $CONST_ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'png', 'mp3', 'mp4'];
    $CONST_TYPE_IMAGE = ['jpg', 'jpeg', 'png'];
    $CONST_TYPE_OTHER_MEDIA = ['gif', 'png', 'mp3', 'mp4'];

    $STATUS_FIELD_IN_RESPONSE = [
        '1'=>'Success',
        '2'=>'Error'
    ]
?>  